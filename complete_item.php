<?php
include_once 'connection.php';

if(isset($_POST['action']) && $_POST['action'] == 'add'){
	$qry="SELECT * FROM items WHERE id=:id";
	$stmt = $db1->prepare($qry);
	$stmt->setFetchMode(PDO::FETCH_ASSOC);
	$stmt->execute(array(":id" => $_POST['item_name']));
	$row=$stmt->fetch();
	$parts=unserialize($row['parts']);

    $outofstock=array();
	foreach ($parts as $key => $value) {
		$sql = "SELECT * FROM total_stock WHERE id = :id";
		$stmt = $db1->prepare($sql);
		$stmt->setFetchMode(PDO::FETCH_ASSOC);
		$stmt->execute(array(":id" => $key));
		$row=$stmt->fetch();
		if($row['stock'] > $value*$_POST['item_qty']){
            
        }else{
            $outofstock[] = $row['parts_name'];
        }
	}

    if (count($outofstock) == 0 ) {
        $sql = "INSERT INTO complete_items (`item_id`, `qty`, `add_date`) VALUES (:item_id, :qty, :add_date)";
        $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $db1->prepare($sql);
        $stmt->execute(array(":item_id" => $_POST['item_name'], ":qty" => $_POST['item_qty'], ":add_date" => date("Y-m-d H:i:s")));
        $comitemid=$db1->lastInsertId();
        foreach ($parts as $key => $value) {
            $usedQty = $value*$_POST['item_qty'];
            $sql = "INSERT INTO stock_out (`parts_id`, `stock`, `used_date` ,`com_item_id`) VALUES (:parts_id, :stock, :used_date ,:com_item_id)";
            $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $stmt = $db1->prepare($sql);
            $stmt->execute(array(":parts_id" => $key, ":stock" => $usedQty, ":used_date" => date("Y-m-d H:i:s"), ":com_item_id" => $comitemid));
            update_parts_qty($key);
        }
        update_pump_qty($_POST['item_name']);
        echo json_encode(array("status" => "ok"));
    }
    else{
        echo json_encode(array("status" => "error", "parts" => implode(", ",$outofstock)));
    }    
    die;
}

if(isset($_POST['action']) && $_POST['action'] == 'edit' && $_POST['id'] > 0){
    $stmt = $db1->prepare("SELECT c.*,i.name FROM `complete_items` c LEFT JOIN `items` i ON c.item_id=i.id WHERE c.id = :id");
    $stmt->execute(array(":id" =>$_POST['id']));
    $row=$stmt->fetch();
    echo json_encode($row);
    die;
}

if(isset($_POST['action']) && $_POST['action'] == 'update'){
    $qry="SELECT * FROM items WHERE id=:id";
    $stmt = $db1->prepare($qry);
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array(":id" => $_POST['edit_item_id']));
    $row=$stmt->fetch();
    $parts=unserialize($row['parts']);
    
    $outofstock=array();
    foreach ($parts as $key => $value) {
        $sql = "SELECT * FROM total_stock WHERE id = :id";
        $stmt = $db1->prepare($sql);
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array(":id" => $key));
        $row=$stmt->fetch();
        if($row['stock'] > $value*$_POST['edit_item_qty']){
            
        }else{
            $outofstock[] = $row['parts_name'];
        }
    }

    if(count($outofstock) == 0){
        $sql = "DELETE FROM complete_items WHERE id=:id";
        $stmt = $db1->prepare($sql);
        $stmt->execute(array(":id" => $_POST['com_item_id']));

        $sql = "DELETE FROM stock_out WHERE com_item_id=:com_item_id";
        $stmt = $db1->prepare($sql);
        $stmt->execute(array(":com_item_id" => $_POST['com_item_id']));

        $sql = "INSERT INTO complete_items (`item_id`, `qty`, `add_date`) VALUES (:item_id, :qty, :add_date)";
        $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $db1->prepare($sql);
        $stmt->execute(array(":item_id" => $_POST['edit_item_id'], ":qty" => $_POST['edit_item_qty'], ":add_date" => date("Y-m-d H:i:s")));
        $comitemid=$db1->lastInsertId();
        foreach ($parts as $key => $value) {
            $usedQty = $value*$_POST['edit_item_qty'];
            $sql = "INSERT INTO stock_out (`parts_id`, `stock`, `used_date` ,`com_item_id`) VALUES (:parts_id, :stock, :used_date ,:com_item_id)";
            $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $stmt = $db1->prepare($sql);
            $stmt->execute(array(":parts_id" => $key, ":stock" => $usedQty, ":used_date" => date("Y-m-d H:i:s"), ":com_item_id" => $comitemid));
            update_parts_qty($key);
        }
        update_pump_qty($_POST['edit_item_id']);
        echo json_encode(array("status" => "ok"));
    }
    else{
        echo json_encode(array("status" => "error", "parts" => implode(", ",$outofstock)));
    }
    die;
}

include_once 'header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <h3 class="mt-4">
                <i class="fa fa-archive"></i>
                Pump Management
                <a class="btn btn-success pull-right" href="" title="Add Item" id="add_item"><i class="fa fa-plus"></i></a>
            </h3>
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
                                    <th> # </th>
                                    <th> Item Name </th>
                                    <th> Qty </th>
                                    <th> Add Date </th>
                                    <th> Action </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                	$sql = "SELECT c.*,i.name FROM `complete_items` c LEFT JOIN `items` i ON c.item_id=i.id";
                                	$stmt = $db1->prepare($sql);
                                	$stmt->setFetchMode(PDO::FETCH_ASSOC);
                                	$stmt->execute();
                                	$index = 0;
                                	while($row = $stmt->fetch()){
                                		$index++;
                                	?>
                                		<tr>
                                			<td><?php echo $index ?></td>
                                			<td><?php echo $row['name']; ?></td>
                                			<td><?php echo $row['qty'] ?></td>
                                			<td><?php echo $row['add_date'] ?></td>
                                			<td> <a href="<?php echo $row["id"]; ?>" class="btn btn-success edit_row text-white" style="margin-left: 25px;"><i class="fa fa-edit"></i></a></td>
                                		</tr>
                                	<?php
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


<!-- Add Complete item Model -->
<div id="compelete_item" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <form action="" method="POST" id="compelete_item_form" class='form-horizontal form-validate'>
                <input type="hidden" name="parts_id" id="parts_id">
                <input type="hidden" name="action" id="action" value="add">
                <div class="modal-header modal-header-primary">
                    <h4 class="modal-title">Add Complete Item</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div id="error-message"></div>
                    <div class="form-group row">
                    	<label for="item" class="control-label col-sm-3">Item Name</label>
                    	 <div class="col-sm-9">
	                    	<select name="item_name" id="item_name" class="form-control">
	                    		<option value="">Select Item</option>
	                    	 	<?php
	                    	 		$qry = "SELECT * FROM items";
	                    	 		$stmt = $db1->prepare($qry);
	                    	 		$stmt->setFetchMode(PDO::FETCH_ASSOC);
	                    	 		$stmt->execute();
	                    	 		while($row = $stmt->fetch()){
	                    	 			echo "<option value='{$row['id']}'>".$row['name']."</option>";
	                    	 		}
	                    	 	?>	                    		
	                    	</select>
	                    </div>
                    </div>
                    <div class="form-group row">
                        <label for="qty" class="control-label col-sm-3" id="rate">Qty</label>
                        <div class="col-sm-9">
                            <input type="number" name="item_qty" id="item_qty" required="true" class="form-control">
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

<!-- Edit Complete Item Model -->

<div id="edit_compelete_item" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <form action="" method="POST" id="edit_compelete_item_form" class='form-horizontal form-validate'>
                <input type="hidden" name="action" id="action" value="update">
                <input type="hidden" name="edit_item_id" id="item_id">
                <input type="hidden" name="com_item_id" id="com_item_id">
                <div class="modal-header modal-header-primary">
                    <h4 class="modal-title" id="edit_modal_title"></h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div id="error-message"></div>
                    <div class="form-group row">
                        <label for="qty" class="control-label col-sm-3" id="rate">Qty</label>
                        <div class="col-sm-9">
                            <input type="number" name="edit_item_qty" id="edit_item_qty" required="true" class="form-control">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                    <button type="submit" id="editsubmititemform" name="editsubmititemform" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
	$(document).ready(function(){
		$('#add_item').click(function(){
			$('#compelete_item').modal('show');
			return false;
		});

		$('#submititemform').click(function(){
            if(checknumeric($('#item_qty').val()))
            {
                var formData=$('#compelete_item_form').serialize();
                $.ajax({
                    url: "complete_item.php",
                    type: "post",
                    data: formData,
                    dataType: "json",
                    success:function(data){
                        if (data.status == 'ok') {
                            window.location.href = 'complete_item.php';
                        }else{
                            $("#snackbar").addClass("error").removeClass("success").html('Qty Out Of Stock - Parts Name :- '+data.parts);
                            $('#error-message').html('<div class="alert alert-danger" role="alert">Qty Out Of Stock - Parts Name :- '+data.parts+'</div>');
                        }
                    }
                });                
            }
            else{
                $("#snackbar").removeClass("success").addClass("error").html("Only integer Value Allow");
                myFunction();
            }
			return false;
		});

       $(".edit_row").click(function () {
        var id = $(this).attr("href");
        $.ajax({
            url: "complete_item.php",
            type: "post",
            data: "action=edit&id=" + id,
            dataType: "json",
            success: function (res) {
                $('#edit_item_qty').val(res.qty);
                $('#item_id').val(res.item_id);
                $('#edit_modal_title').html(res.name);                
                $('#com_item_id').val(res.id);
                $('#edit_compelete_item').modal('show');
            }
        });
        return false;
        });

        $('#editsubmititemform').click(function(){
            var editformData=$('#edit_compelete_item_form').serialize();
            if(checknumeric($('#edit_item_qty').val())){
                $.ajax({
                url: "complete_item.php",
                type: "post",
                data: editformData,
                dataType: "json",
                success:function(data){
                    if (data.status == 'ok') {
                        window.location.href = 'complete_item.php';
                    }else{
                        $("#snackbar").addClass("error").removeClass("success").html('Qty Out Of Stock - Parts Name :- '+data.parts);
                        $('#error-message').html('<div class="alert alert-danger" role="alert">Qty Out Of Stock - Parts Name :- '+data.parts+'</div>');
                        
                        }
                    }
                });
            }
            else{
                $("#snackbar").removeClass("success").addClass("error").html("Only integer Value Allow");
                myFunction();
            }
            return false;
        });       
	});
    function checknumeric(value) {
        return /^[0-9]*$/.test(value);
    }
</script>

<?php
include_once 'footer.php';
?>