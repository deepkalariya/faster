<?php
include_once 'connection.php';
include_once 'auth.php';

if (count($_POST) && isset($_POST['submititemform'])) { //echo $_POST['item_name']; die();
    if ($_POST["parts_id"] > 0) {
        /*$sql = "UPDATE total_stock SET item_name=:item_name, item_rate=:item_rate WHERE id=:id";
        $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $db1->prepare($sql);
        $stmt->execute(array(":item_name" => $_POST['item_name'], ":item_rate" => $_POST['item_rate'], ":id" => $_POST["item_id"]));*/

        $partrow=array();
        $validflag=true;
        foreach ($_POST['selectedparts'] as $key => $value) {
            $peta_item_qty=$_POST['parts_qty'] * $_POST['used_qty'][$value];
            $stmt = $db1->prepare("SELECT * FROM `total_stock` WHERE id=$value");
            $stmt->execute();
            $prow=$stmt->fetch();
            if($prow['stock']>=$peta_item_qty){
                $partrow[]=$prow;
            }else{
                $validflag=false;
                $_SESSION['message'] = '<i class="fa fa-save"></i> '.$prow['parts_name'].' is out of stock.';
            }
        }

        $parts=array();
        if (isset($_POST['selectedparts'])) {
            foreach ($_POST['selectedparts'] as $key => $value) {
                $parts[$value] = ($_POST['used_qty'][$value] > 0) ? $_POST['used_qty'][$value] : 1;
            }
        }

       $sql = "DELETE FROM stock_out WHERE main_item_id=:main_item_id";
       $stmt = $db1->prepare($sql);
       $stmt->execute(array(":main_item_id" => $_POST['parts_id']));

       $sql ="UPDATE total_stock SET cat_id=:cat_id, peta_items=:peta_items WHERE id=:id";
       $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
       $stmt = $db1->prepare($sql);
       $stmt->execute(array(":peta_items" => serialize($parts), ":cat_id" => $_POST['edit_cetegory_name'], ":id" => $_POST["parts_id"]));


       if ($validflag && $_POST['is_peta_item'] == 0 && count($partrow) > 0) {
            foreach($partrow as $row){
                $peta_item_qty=$_POST['used_qty'][$row['id']];
                $sql = "INSERT INTO stock_out (`parts_id`, `stock`, `used_date` , `main_item_id`) VALUES (:parts_id, :stock, :used_date, :main_item_id)";
                $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                $stmt = $db1->prepare($sql);
                $stmt->execute(array(":parts_id" => $row['id'], ":stock" => $peta_item_qty, ":used_date" => date("Y-m-d H:i:s"), ":main_item_id" => $_POST['parts_id']));
                update_parts_qty($row['id']);
            }
        }
        $_SESSION['message'] = '<i class="fa fa-save"></i> Parts updated successfully.';
        session_write_close();
        header("location: stocks.php?q={$_POST['edit_cetegory_name']}");

    } else {
        // peta item validation
        $partrow=array();
        $validflag=true;
        foreach ($_POST['selectedparts'] as $key => $value) {
            $peta_item_qty=$_POST['parts_qty'] * $_POST['used_qty'][$value];
            $stmt = $db1->prepare("SELECT * FROM `total_stock` WHERE id=$value");
            $stmt->execute();
            $prow=$stmt->fetch();
            if($prow['stock']>=$peta_item_qty){
                $partrow[]=$prow;
            }else{
                $validflag=false;
                $_SESSION['message'] = '<i class="fa fa-save"></i> '.$prow['parts_name'].' is out of stock.';
            }
        }

        $param=array(":parts_name" => $_POST['parts_name'], ":cat_id" => $_POST['cetegory_name'], ":stock" => $_POST['parts_qty'], ":alert_limit" => $_POST['alert_limit'], ":is_peta_item" =>$_POST['is_peta_item']);
        $sql = "INSERT INTO total_stock (`parts_name`, `cat_id`, `stock`, `alert_limit`, `is_peta_item`";
        $parts=array();
        if ($_POST['is_peta_item'] == '0' && isset($_POST['selectedparts'])) {
            $sql.=",peta_items";
            foreach ($_POST['selectedparts'] as $key => $value) {
                $parts[$value] = ($_POST['used_qty'][$value] > 0) ? $_POST['used_qty'][$value] : 1;
            }
        }

        $sql.=") VALUES (:parts_name, :cat_id, :stock, :alert_limit, :is_peta_item";
        if(count($parts) > 0){
            $sql.=",:peta_items";
            $param[':peta_items']=serialize($parts);
        }
        $sql.=")";

        $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $db1->prepare($sql);
        $stmt->execute($param);
        $parts_id = $db1->lastInsertId();

        if ($validflag && $_POST['is_peta_item'] == 0 && count($partrow) > 0) {
            foreach($partrow as $row){
                $peta_item_qty=$_POST['parts_qty'] * $_POST['used_qty'][$row['id']];
                $sql = "INSERT INTO stock_out (`parts_id`, `stock`, `used_date` , `main_item_id`) VALUES (:parts_id, :stock, :used_date, :main_item_id)";
                $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                $stmt = $db1->prepare($sql);
                $stmt->execute(array(":parts_id" => $row['id'], ":stock" => $peta_item_qty, ":used_date" => date("Y-m-d H:i:s"), ":main_item_id" => $parts_id));
                update_parts_qty($row['id']);
            }
        }

        $sql = "INSERT INTO stock_in (`parts_id`, `stock`, `add_date`) VALUES (:parts_id, :stock, :add_date)";
        $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $db1->prepare($sql);
        $stmt->execute(array(":parts_id" => $parts_id, ":stock" => $_POST['parts_qty'], ":add_date" => date("Y-m-d H:i:s")));
        $_SESSION['message'] = '<i class="fa fa-save"></i> Parts saved successfully.';
        session_write_close();
        header("location: stocks.php?q={$_POST['cetegory_name']}");
    }
    die;
}

if (isset($_POST['action']) && $_POST['action'] == 'add_new_stock' && $_POST['id'] > 0 && $_POST['qty'] > 0) {
    $subpart=array();
    $stmt = $db1->prepare("SELECT * FROM total_stock WHERE id=:id");
    $stmt->execute(array(":id"=>$_POST['id']));
    $row = $stmt->fetch();
    if(!empty($row['peta_items'])){
        $parts=unserialize($row['peta_items']);

        $partrow=array();
            $validflag=true;
            foreach ($parts as $key => $value) {
                $peta_item_qty=$_POST['qty'] * $value;
                $stmt = $db1->prepare("SELECT * FROM `total_stock` WHERE id=$key");
                $stmt->execute();
                $prow=$stmt->fetch();
                if($prow['stock']>=$peta_item_qty){
                    $partrow[]=array("id"=>$key, "used_qty" => $value);
                }else{
                    $validflag=false;
                    $_SESSION['message'] = '<i class="fa fa-save"></i> '.$prow['parts_name'].'is out of stock.';
                }
            }

        if ($validflag && count($partrow) > 0) {
            foreach($partrow as $row){
                $peta_item_qty=$_POST['qty'] * $row['used_qty'];
                $sql = "INSERT INTO stock_out (`parts_id`, `stock`, `used_date` , `main_item_id`) VALUES (:parts_id, :stock, :used_date, :main_item_id)";
                $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
                $stmt = $db1->prepare($sql);
                $stmt->execute(array(":parts_id" => $row['id'], ":stock" => $peta_item_qty, ":used_date" => date("Y-m-d H:i:s"), ":main_item_id" => $_POST['id']));
                $subpart[$row['id']]=update_parts_qty($row['id']);
            }
        }
    }

    $sql = "INSERT INTO stock_in (`parts_id`, `stock`, `add_date`) VALUES (:parts_id, :stock, :add_date)";
    $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $stmt = $db1->prepare($sql);
    
    if ($stmt->execute(array(":parts_id" => $_POST['id'], ":stock" => $_POST['qty'], ":add_date" => date("Y-m-d H:i:s")))) {
        $status = "ok";
    } else {
        $status = "error";
    }
    $total_qty = update_parts_qty($_POST['id']);
    echo json_encode(array("status" => $status, "total_qty" => $total_qty, "sub_part" => $subpart));
    die;
}

if (isset($_POST['action']) && $_POST['action'] == 'save_edit' && $_POST['id'] > 0) {
    $stmt = $db1->prepare("UPDATE total_stock SET parts_name=:parts_name WHERE id=:id");
    if ($stmt->execute(array(":parts_name" => $_POST['parts_name'], ":id" => $_POST['id']))) {
        echo "ok";
    } else {
        echo "error";
    }
    
    die;
}

if (isset($_POST['action']) && $_POST['action'] == 'save_edit_alert_limit' && $_POST['id'] > 0) {
    $stmt = $db1->prepare("UPDATE total_stock SET alert_limit=:alert_limit WHERE id=:id");
    if ($stmt->execute(array(":alert_limit" => $_POST['alert_limit'], ":id" => $_POST['id']))) {
        echo "ok";
    } else {
        echo "error";
    }
    
    die;
}

if (isset($_POST['action']) && $_POST['action'] == 'edit_peta_item' && $_POST['id'] > 0) {
    $stmt = $db1->prepare("SELECT * FROM total_stock WHERE id=:id");
    $stmt->execute(array(":id" => $_POST['id']));
    $row = $stmt->fetch();
    $peta_items=unserialize($row['peta_items']);
    $row['peta_items']=$peta_items;
    echo json_encode($row);
    die;
}

if(isset($_GET['action']) && $_GET['action'] == 'delete'){
    if($_GET['peta_item'] == 'yes'){
        $stmt = $db1->prepare("SELECT * FROM `total_stock` WHERE `is_peta_item` = '0' AND `peta_items` != ''");
        $stmt->execute();
        if($stmt->rowCount()){
            while($row=$stmt->fetch()){
                $peta_item_parts=unserialize($row['peta_items']);
                if(array_key_exists($_GET['id'], $peta_item_parts)){
                    echo json_encode(array("status"=>"error", "message"=>$row['parts_name']));
                    break;
                }
                else{
                    $stmtdelete = $db1->prepare("DELETE FROM `total_stock` WHERE id=:id");
                    $stmtdelete->execute(array(':id'=>$_GET['id']));
                    echo json_encode(array("status"=>"ok", "message"=>"deleted successfully"));
                    break;
                }
            }
        }
        else{
            $stmtdelete = $db1->prepare("DELETE FROM `total_stock` WHERE id=:id");
            $stmtdelete->execute(array(':id'=>$_GET['id']));
            echo json_encode(array("status"=>"ok", "message"=>"deleted successfully"));
        }
    }
    else{
        $stmt = $db1->prepare("SELECT * FROM `items`");
        $stmt->execute();
        if($stmt->rowCount()){
            while($row=$stmt->fetch()){
                $peta_item_parts=unserialize($row['parts']);
                if(array_key_exists($_GET['id'], $peta_item_parts)){
                    echo json_encode(array("status"=>"error", "message"=>$row['name'].(($row['type'] == 'p') ? " Pump" : " Moter")));
                    break;
                }
                else{
                    $stmtdelete = $db1->prepare("DELETE FROM `total_stock` WHERE id=:id");
                    $stmtdelete->execute(array(':id'=>$_GET['id']));
                    echo json_encode(array("status"=>"ok", "message"=>"deleted successfully"));
                    break;
                }
            }
        }
        else{
            $stmtdelete = $db1->prepare("DELETE FROM `total_stock` WHERE id=:id");
            $stmtdelete->execute(array(':id'=>$_GET['id']));
            echo json_encode(array("status"=>"ok", "message"=>"deleted successfully"));
        }
    }

    die;
}

include_once 'header.php';

$stmt = $db1->prepare("SELECT * FROM options WHERE parameter=:parameter");
$stmt->setFetchMode(PDO::FETCH_ASSOC);
$stmt->execute(array(":parameter" => "start_inv_no"));
$row = $stmt->fetch();
$invoice_number = empty($row["value"]) ? 0 : $row["value"];
?>
<div class="container">
    <div class="row">
        <div class="col-sm-6">
            <h3 class="mt-4">
                <i class="fa fa-archive"></i>
                Stocks Management                
            </h3>
        </div>
        <div class="col-sm-5 mt-4 text-sm-right">
            <form method="get">
                <div class="row">
                    <div class="col-sm-10">
                        <select name="q" id="cetegory_name" class="form-control">
                                <option value="">Cetegory Item</option>
                                <?php
                                    $qry = "SELECT * FROM stock_category";
                                    $stmt = $db1->prepare($qry);
                                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                                    $stmt->execute();
                                    while($row = $stmt->fetch()){
                                        echo "<option value='{$row['id']}' ".((isset($_GET['q']) && $_GET['q'] == $row['id']) ? 'selected' : '').">".$row['cat_name']."</option>";
                                    }
                                ?>                              
                            </select>
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-success" title="search"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-1 mt-4">
            <a class="btn btn-success pull-right" href="" title="Add Parts" id="new_parts"><i class="fa fa-plus"></i></a>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-color box-bordered">
                <div class="box-content nopadding">
                    <form method="post" id="affliate_level_form" action="">
                        <table id="item_options" class="table table-hover table-nomargin item_table">
                            <thead>
                                <tr>
                                    <th width="100"> # </th>
                                    <th width="340"> Parts Name </th>
                                    <th width="230"> Alert Limit </th>
                                    <th width="100"> Qty </th>
                                    <th> Add Qty </th>
									<th colspan="3"> Action </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM `total_stock` WHERE 1";
                                $params=array();

                                if (isset($_GET['q'])) {
                                    $sql .= " AND cat_id LIKE :cat_id";
                                    $params[':cat_id'] = '%' . $_GET['q'] . '%';
                                }

                                $stmt=$db1->prepare($sql);
                                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                                $stmt->execute($params);
                                $index = 0;
                                if ($stmt->rowCount()) {
                                    while ($row = $stmt->fetch()) {
                                        $index++;
                                ?>
                                        <tr class="new_row">
                                            <td> <?php echo $index; ?>
                                            </td>
                                            <td>
                                                <div class="display_parts_name">
                                                    <span id="parts_name_<?php echo $row['id']; ?>"><?php echo $row['parts_name'] ; ?></span>
                                                    <a href="" class="edit_parts_name_btn btn btn-success btn-sm"><i class="fa fa-edit"></i></a>
                                                </div>
                                                <div class="edit_parts_name" style="display: none;">
                                                    <input id="parts_name_<?php echo $row['id']; ?>" rel-data-id="<?php echo $row['id']; ?>" type="text" value="<?php echo $row['parts_name']; ?>">
                                                    <a href="" class="save_parts_name btn btn-success btn-sm"><i class="fa fa-save"></i></a> &nbsp;&nbsp;
                                                    <a href="" class="edit_cancel_parts_btn btn btn-danger btn-sm"><i class="fa fa-times"></i></a>
                                                </div>
                                            </td>
                                            <td>
                                                <div class="display_alert_limit">
                                                    <span id="alert_limit_<?php echo $row['id']; ?>"><?php echo $row['alert_limit']; ?></span>
                                                    <a href="" class="edit_alert_limit_btn btn btn-success btn-sm"><i class="fa fa-edit"></i></a>
                                                </div>
                                                <div class="edit_alert_limit" style="display: none;">
                                                    <input id="alert_limit_<?php echo $row['id']; ?>" rel-data-id="<?php echo $row['id']; ?>" type="text" value="<?php echo $row['alert_limit']; ?>" style="width: 100px;">  
                                                    <a href="" class="save_alert_limit btn btn-success btn-sm"><i class="fa fa-save"></i></a> &nbsp;
                                                    <a href="" class="edit_cancel_alert_limit_btn btn btn-danger btn-sm"><i class="fa fa-times"></i></a>
                                                </div>
                                            </td>
                                            <td><?php echo $row['stock']; ?></td>
                                            <td>
                                                <input id="add_stock_<?php echo $row['id']; ?>" rel-data-id="<?php echo $row['id']; ?>" type="number" value="" style="width: 100px;"> 
                                                <a href="" class="add_stock btn btn-success btn-sm"><i class="fa fa-plus"></i></a>
                                            </td>
                                            <td>
                                                <?php if($row['is_peta_item'] === '0') { ?>
                                                    <a href="<?php echo $row["id"]; ?>" class="btn btn-success btn-sm open_edit_peta_item" data-toggle="modal"><i class="fa fa-edit"></i></a>
                                                <?php } ?>
                                            </td>

                                            <td><a href="view_stock.php?id=<?php echo $row['id']; ?>" class="btn btn-success btn-sm"><i class="fa fa-eye" aria-hidden="true"></i></a></td>
                                            <td>
                                                <a href="#" class="btn btn-danger  btn-sm delete_row" data-id="<?php echo $row["id"]; ?>" data-peta-item="<?php echo ($row['is_peta_item'] === '0') ? 'no' : 'yes'; ?>">
                                                    <i class="fa fa-trash"></i>
                                                </a>
                                            </td>

                                        </tr>
                                <?php
                                    }
                                }
                                ?>
                            </tbody>
                        </table>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="parts_add" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <form action="" method="POST" id="part_form" class='form-horizontal form-validate'>
                <input type="hidden" name="parts_id" id="parts_id">
                <div class="modal-header modal-header-primary">
                    <h4 class="modal-title">Add Parts</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">

                    <div class="form-group row">
                        <label for="parts_name" class="control-label col-sm-3">Parts Name</label>
                        <div class="col-sm-9">
                            <input type="text" name="parts_name" id="parts_name" required="true" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="Cetegory" class="control-label col-sm-3">Cetegory Name</label>
                         <div class="col-sm-9">
                            <select name="cetegory_name" id="cetegory_name" class="form-control" required="true">
                                <option value="">Cetegory Item</option>
                                <?php
                                    $qry = "SELECT * FROM stock_category";
                                    $stmt = $db1->prepare($qry);
                                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                                    $stmt->execute();
                                    while($row = $stmt->fetch()){
                                        echo "<option value='{$row['id']}'>".$row['cat_name']."</option>";
                                    }
                                ?>                              
                            </select>
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="parts_qty" class="control-label col-sm-3" id="rate">Qty</label>
                        <div class="col-sm-9">
                            <input type="number" name="parts_qty" id="parts_qty" required="true" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="alert_limit" class="control-label col-sm-3" id="rate">Alert Limit</label>
                        <div class="col-sm-9">
                            <input type="number" name="alert_limit" id="alert_limit" required="true" class="form-control">
                        </div>
                    </div>
                    <div class="form-group row">
                        <label for="is_peta_item" class="control-label col-sm-3" id="is_peta_item">Is Main Item ?</label>
                        <div class="col-sm-9">
                            <input type="radio" id="is_peta_item_yes" name="is_peta_item" value="0" checked> Yes
                            <input type="radio" id="is_peta_item_no" name="is_peta_item" value="1"> No (Peta Item)
                        </div>
                    </div>

                    <div class="peta_item" style="display: none;">
                        <?php
                            $qry="SELECT * FROM `total_stock` WHERE `is_peta_item` = '1'";

                            $stmt = $db1->prepare($qry);

                            $stmt->setFetchMode(PDO::FETCH_ASSOC);

                            $stmt->execute();
                        ?>

                        <div class="row">
                            <div class="col-sm">
                                <p><h4>Used Peta Item</h4></p>
                                <table class="table">
                                    <?php
                                        while($row=$stmt->fetch()){
                                        ?>
                                            <tr>
                                                <td><input type="checkbox" id="selectedparts_<?php echo $row['id'];?>" name="selectedparts[]" value="<?php echo $row['id'];?>"></td>
                                                <td><?php echo $row['parts_name']?></td>
                                                <td><input type="text" id="used_qty_<?php echo $row['id'];?>" name="used_qty[<?php echo $row['id'];?>]"  class="form-control" style="width:200px"></td>
                                            </tr>
                                        <?php
                                        }
                                    ?>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" id="submititemform" name="submititemform" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div id="edit_peta_item" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <form action="" method="POST" id="edit_peta_item_form" class='form-horizontal form-validate'>
                <div class="modal-header modal-header-primary">
                    <input type="hidden" name="parts_id" id="parts_id">
                    <h4 class="modal-title">Edit Parts</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="form-group row">
                        <label for="Cetegory" class="control-label col-sm-3">Cetegory Name</label>
                         <div class="col-sm-9">
                            <select name="edit_cetegory_name" id="cetegory_name" class="form-control" required="true">
                                <option value="">Cetegory Item</option>
                                <?php
                                    $qry = "SELECT * FROM stock_category";
                                    $stmt = $db1->prepare($qry);
                                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                                    $stmt->execute();
                                    while($row = $stmt->fetch()){
                                        echo "<option value='{$row['id']}'>".$row['cat_name']."</option>";
                                    }
                                ?>                              
                            </select>
                        </div>
                    </div>
                    <?php
                        $qry="SELECT * FROM `total_stock` WHERE `is_peta_item` = '1'";

                        $stmt = $db1->prepare($qry);

                        $stmt->setFetchMode(PDO::FETCH_ASSOC);

                        $stmt->execute();
                    ?>

                    <div class="row">
                        <div class="col-sm">
                            <p><h4>Peta List</h4></p>
                            <table class="table"  id="peta-item-warp">
                                <?php
                                    while($row=$stmt->fetch()){
                                    ?>
                                        <tr>
                                            <td><input type="checkbox" id="edit_selectedparts_<?php echo $row['id'];?>" name="selectedparts[]" value="<?php echo $row['id'];?>"></td>
                                            <td><?php echo $row['parts_name']?></td>
                                            <td><input type="text" id="edit_used_qty_<?php echo $row['id'];?>" name="used_qty[<?php echo $row['id'];?>]"  class="form-control" style="width:200px"></td>
                                        </tr>
                                    <?php
                                    }
                                ?>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" id="editsubmititemform" name="submititemform" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // var del_obj = null;
        $("#new_parts").click(function() {
            $("#parts_add #parts_name").val("");
            $("#parts_add #parts_qty").val("");
            $("#parts_add #parts_id").val(0);
            $("#parts_add").modal("show");
            $("#parts_add input[name='is_peta_item']:checked").trigger('click');
            return false;
        });

        $(".edit_parts_name_btn").click(function() {
            var obj = $(this).parents(".display_parts_name");
            $(obj).hide();
            $(obj).next().show();
            return false;
        });

        $(".edit_cancel_parts_btn").click(function() {
            var obj = $(this).parents(".edit_parts_name");
            $(obj).hide();
            $(obj).prev().show();
            return false;
        });

        $(".save_parts_name").click(function() {
            var obj = $(this).parents(".edit_parts_name");
            var val = $(obj).find("input").val();
            var id = $(obj).find("input").attr("rel-data-id");
            if (val != "") {
                $.ajax({
                    url: "stocks.php",
                    type: "post",
                    data: "action=save_edit&parts_name=" + val + "&id=" + id,
                    success: function(res) {
                        if (res == "ok") {
                            $(obj).hide();
                            $(obj).prev().show().find("span").html(val);
                            $("#snackbar").removeClass("error").addClass("success").html("Parts name is updated.");
                            myFunction();
                        } else {
                            $("#snackbar").removeClass("success").addClass("error").html("Error in save parts name");
                            myFunction();
                        }
                    }
                });
            } else {
                $("#snackbar").removeClass("success").addClass("error").html("Please enter parts name.");
                myFunction();
            }
            return false;
        });

        $(".edit_alert_limit_btn").click(function() {
            var obj = $(this).parents(".display_alert_limit");
            $(obj).hide();
            $(obj).next().show();
            return false;
        });

        $(".edit_cancel_alert_limit_btn").click(function() {
            var obj = $(this).parents(".edit_alert_limit");
            $(obj).hide();
            $(obj).prev().show();
            return false;
        });

        $(".save_alert_limit").click(function() {
            var obj = $(this).parents(".edit_alert_limit");
            var val = $(obj).find("input").val();
            var id = $(obj).find("input").attr("rel-data-id");
            if (val != "" && checknumeric(val)) {
                $.ajax({
                    url: "stocks.php",
                    type: "post",
                    data: "action=save_edit_alert_limit&alert_limit=" + val + "&id=" + id,
                    success: function(res) {
                        if (res == "ok") {
                            $(obj).hide();
                            $(obj).prev().show().find("span").html(val);
                            $("#snackbar").removeClass("error").addClass("success").html("Alert Limit is updated.");
                            myFunction();
                        } else {
                            $("#snackbar").removeClass("success").addClass("error").html("Error in save Alert Limit");
                            myFunction();
                        }
                    }
                });
            } else {
                $("#snackbar").removeClass("success").addClass("error").html("Please Enter Valid Alert Limit.");
                myFunction();
            }
            return false;
        });

        $(".add_stock").click(function() {
            var obj = $(this).parent();
            var val = $(obj).find("input").val();
            var id = $(obj).find("input").attr("rel-data-id");
            if(checknumeric(val)){
                if (val > 0) {
                    $.ajax({
                        url: "stocks.php",
                        type: "post",
                        data: "action=add_new_stock&qty=" + val + "&id=" + id,
                        dataType: 'json',
                        success: function(res) {
                            if (res.status == "ok") {
                                $.each(res.sub_part,function(k,v){
                                    $('#add_stock_'+k).parent().prev().html(v);
                                });
                                $(obj).prev().html(res.total_qty);
                                $(obj).find("input").val('');
                                $("#snackbar").removeClass("error").addClass("success").html("Qty updated.");
                                myFunction();
                            } else {
                                $("#snackbar").removeClass("success").addClass("error").html("Error in update qty");
                                myFunction();
                            }
                        }
                    });
                } else {
                    $("#snackbar").removeClass("success").addClass("error").html("Please enter number of qty.");
                    myFunction();
                }
            }
            else{
                $("#snackbar").removeClass("success").addClass("error").html("Only integer Value Allow");
                myFunction();
            }
            return false;
        });

        $("#is_peta_item_yes").click(function(){
            $('#parts_qty').val('1').attr('readonly',true);
            $('.peta_item').show();
        });
        $("#is_peta_item_no").click(function(){
            $('#parts_qty').val('').attr('readonly',false);
            $('.peta_item').hide();
        });

        $(".open_edit_peta_item").click(function(){
            var id = $(this).attr("href");
            $.ajax({
                url: "stocks.php",
                type: "post",
                data: "action=edit_peta_item&id=" + id,
                dataType: "json",
                success: function (res) {
                    console.log(res);
                    if(res.is_peta_Item == 0){
                        $("#edit_peta_item input#edit_is_peta_item_yes").prop('checked', true).trigger('click');
                    }else{
                        $("#edit_peta_item input#edit_is_peta_item_no").prop('checked', true).trigger('click');
                    }
                    $('#peta-item-warp tr').each(function(){
                        $(this).find('input[type="checkbox"]').prop('checked',false);
                        $(this).find('input[type="text"]').val('');
                    });
                    $.each(res.peta_items,function(k,v){
                        $('#edit_selectedparts_'+k).prop('checked', true);
                        $('#edit_used_qty_'+k).val(v);
                    });
                    $('#edit_peta_item #cetegory_name').val(res.cat_id);
                    $('#edit_peta_item #parts_id').val(res.id);
                    $('#edit_peta_item').modal('show');
                }
            });
            return false;
        });

        $('#editsubmititemform').click(function(){
            var formData=$('#edit_peta_item_form').serialize();
            $.ajax({
                url: "stocks.php",
                type: "post",
                data: formData,
                dataType: "json",
                success: function (res) {

                }
            })
            
        });

        $('.delete_row').click(function(){
            var obj = $(this);
            var del_id = $(this).data('id');
            var peta_item = $(this).data('peta-item');
            Swal.fire({
                title: 'Are you sure?',
                text: "You won't be able to revert this!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, delete it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: "stocks.php",
                        type: "get",
                        data: "id="+del_id+"&action=delete&peta_item="+peta_item,
                        dataType: 'json',
                        success: function(data){
                            if(data.status == 'ok'){
                                Swal.fire(
                                    'Deleted!',
                                    'Your file has been deleted.',
                                    'success'
                                )
                                $(obj).parent().parent().remove();
                            }
                            if(data.status == 'error'){
                                Swal.fire(
                                    'Error!',
                                    'This Part is Used in '+data.message+'.',
                                    'error'
                                )
                            }
                        }
                    });
                }
            })
            return false;
        });
    });

    $(".edit_row").click(function() {
        var id = $(this).attr("href");
        //$("#quickpost_id").val(id);
        $.ajax({
            url: "settings.php",
            type: "post",
            data: "action=edit&id=" + id,
            dataType: "json",
            success: function(res) {
                $("#item_add #item_name").val(res.item_name);
                $("#item_add #item_rate").val(res.item_rate);
                $("#item_add #item_id").val(res.id);

                $("#item_add").modal("show");
            }
        });
        return false;
    });

    /* function init_delete_row() {
     $('#item_options').find(".new_row").each(function () {
     $(this).find(".delete_row").click(function () {
     del_obj = $(this);
     $('#delete-confirmation-modal').modal('show');
     return false;
     });
     $(this).removeClass("new_row");
     });
     } */

     function checknumeric(value) {
        return /^[0-9]*$/.test(value);
    }
</script>
<?php include_once 'footer.php'; ?>