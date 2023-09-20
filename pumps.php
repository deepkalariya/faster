<?php

include_once 'connection.php';


if (isset($_POST['action']) && $_POST['action'] == 'add') {
    $parts = array();
    foreach ($_POST['selectedparts'] as $key => $value) {
        $parts[$value] = $_POST['used_qty'][$value];
    }
    $sql = "INSERT INTO items (name,type,rate,parts) VALUES (:name,:type,:rate,:parts)";
    $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $stmt = $db1->prepare($sql);
    $stmt->execute(array(":name" => $_POST['item_name'], ":type" => $_POST['item_type'], ":rate" => $_POST['item_cost'], "parts" => serialize($parts)));
    if ($_POST['item_type'] == 'p') {
        $_SESSION['message'] = '<i class="fa fa-save"></i> Pump Add successfully.';
        session_write_close();
        echo json_encode(array("status" => "ok", "Message" => "Pump Add Successfully"));
    }
    if ($_POST['item_type'] == 'm') {
        $_SESSION['message'] = '<i class="fa fa-save"></i> Motor Add successfully.';
        session_write_close();
        echo json_encode(array("status" => "ok", "Message" => "Motor Add Successfully"));
    }
    die;
}

if (isset($_POST['action']) && $_POST['action'] == 'update') {
    $parts = array();
    foreach ($_POST['selectedparts'] as $key => $value) {
        $parts[$value] = $_POST['used_qty'][$value];
    }
    $sql = "UPDATE items SET name=:name, type=:type, rate=:rate, parts=:parts WHERE id=:id";
    $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $stmt = $db1->prepare($sql);
    $stmt->execute(array(":name" => $_POST['item_name'], ":type" => $_POST['item_type'], ":rate" => $_POST['item_cost'], "parts" => serialize($parts), ":id" => $_POST["item_id"]));
    if ($_POST['item_type'] == 'p') {
        $_SESSION['message'] = '<i class="fa fa-save"></i> Pump Update Successfully.';
        session_write_close();
        echo json_encode(array("status" => "ok", "Message" => "Pump Update Successfully"));
    }
    if ($_POST['item_type'] == 'm') {
        $_SESSION['message'] = '<i class="fa fa-save"></i> Motor Update Successfully.';
        session_write_close();
        echo json_encode(array("status" => "ok", "Message" => "Motor Update Successfully"));
    }
    die;
}

if (isset($_POST['action']) && $_POST['action'] == 'edit' && $_POST['id'] > 0) {
    $stmt = $db1->prepare("SELECT * FROM items WHERE id=:id");
    $stmt->execute(array(":id" => $_POST['id']));
    $row = $stmt->fetch();
    $parts = unserialize($row['parts']);
    $row['parts'] = $parts;
    echo json_encode($row);
    die;
}

include_once 'header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-sm-6">
            <h3 class="mt-4">
                <i class="fa fa-gear"></i>
                Pumps Or Motors
            </h3>
        </div>
        <div class="col-sm-5 mt-4 text-sm-right">
            <form method="get">
                <div class="row">
                    <div class="col-sm-5">
                        <input type="text" class="form-control" name="q" value="<?php echo isset($_GET['q']) ? $_GET['q'] : ''; ?>">
                    </div>
                    <div class="col-sm-5">
                        <select name="type"  class="form-control" id="type">
                            <option value="" selected="">All</option>
                            <option value="p">Pump</option>
                            <option value="m">Motor</option>
                        </select>
                    </div>
                    <div class="col-sm-2">
                        <button type="submit" class="btn btn-success" title="search"><i class="fa fa-search"></i></button>
                    </div>
                </div>
            </form>
        </div>
        <div class="col-sm-1 mt-4">
            <a class="btn btn-success pull-right" href="" title="Pump" id="new_item"><i class="fa fa-plus"></i></a>
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
                                    <th> Item Type </th>
                                    <th> Rate (in <?php echo $currency; ?>) </th>
                                    <th> Action </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT * FROM `items` WHERE 1";
                                $params = array();

                                if (isset($_GET['q'])) {
                                    $sql .= " AND name LIKE :item_name";
                                    $params[':item_name'] = '%' . $_GET['q'] . '%';
                                }
                                if (isset($_GET['type'])) {
                                    $sql .= " AND type LIKE :type";
                                    $params[':type'] = '%' . $_GET['type'] . '%';
                                }
                                $stmt = $db1->prepare($sql);
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
                                            <td> <?php echo $row['name']; ?>
                                            </td>
                                            <td> <?php echo ($row['type'] == 'p') ? "Pump" : "Motor"; ?></td>
                                            <td><?php echo custom_number_formate($row['rate']); ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo $row["id"]; ?>" class="btn btn-success edit_row text-white" style="margin-left: 25px;">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <!-- <a href="#" class="btn btn-danger delete_row text-white" style="margin-left: 25px;" data-id="<?php echo $row["id"]; ?>" >
                                                    <i class="fa fa-trash"></i>
                                                </a> -->
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

<div id="item_add" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <form action="" method="POST" id="add_pump" class='form-horizontal form-validate'>
                <input type="hidden" name="item_id" id="item_id">
                <input type="hidden" name="action" id="action" value="">
                <div class="modal-header modal-header-primary">
                    <h4 class="modal-title">Pumps Or Motors</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="row">
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="item_name" class="control-label col-sm">Item Name</label>
                                <div class="col-sm-9">
                                    <input type="text" name="item_name" id="item_name" required="true" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="item" class="control-label col-sm-12">Item Type</label>
                                <div class="col-sm-12">
                                    <select name="item_type" id="item_type" class="form-control">
                                        <option value="p" selected="">Pump</option>
                                        <option value="m">Motor</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-sm">
                            <div class="form-group">
                                <label for="item_name" class="control-label col-sm">Cost</label>
                                <div class="col-sm-9">
                                    <input type="text" name="item_cost" id="item_cost" required="true" class="form-control">
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php
                    $qry = "SELECT * FROM `total_stock` WHERE `is_peta_item` = '0'";

                    $stmt = $db1->prepare($qry);

                    $stmt->setFetchMode(PDO::FETCH_ASSOC);

                    $stmt->execute();
                    ?>

                    <div class="row">
                        <div class="col-sm">
                            <p>
                            <h4>Parts List</h4>
                            </p>
                            <table class="table">
                                <?php
                                while ($row = $stmt->fetch()) {
                                ?>
                                    <tr>
                                        <td><input type="checkbox" id="selectedparts_<?php echo $row['id']; ?>" name="selectedparts[]" value="<?php echo $row['id']; ?>"></td>
                                        <td><?php echo $row['parts_name'] ?></td>
                                        <td><input type="text" id="used_qty_<?php echo $row['id']; ?>" name="used_qty[<?php echo $row['id']; ?>]" class="form-control" style="width:200px"></td>
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
                    <button type="submit" id="submititemform" name="submititemform" class="btn btn-primary">Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        $("#new_item").click(function() {
            $("#item_add #item_name").val("");
            $("#item_add #item_type").val("p");
            $("#item_add #item_cost").val("");
            $("#item_add #action").val("add");
            $("#item_add #item_id").val(0);
            $("#item_add").modal("show");
            return false;
        });
        $('#submititemform').click(function() {
            var formData = $('#add_pump').serialize();
            $.ajax({
                url: "pumps.php",
                type: "post",
                data: formData,
                dataType: "json",
                success: function(data) {
                    if (data.status == "ok") {
                        window.location.href = 'pumps.php';
                    }
                }
            });
            return false;
        });

        $(".edit_row").click(function() {
            var id = $(this).attr("href");
            $.ajax({
                url: "pumps.php",
                type: "post",
                data: "action=edit&id=" + id,
                dataType: "json",
                success: function(res) {
                    $("#item_add #item_name").val(res.name);
                    $("#item_add #item_type").val(res.type);
                    $("#item_add #item_cost").val(res.rate);
                    $("#item_add #action").val("update");
                    $("#item_add #item_id").val(res.id);
                    $.each(res.parts, function(k, v) {
                        $('#selectedparts_' + k).prop('checked', true);
                        $('#used_qty_' + k).val(v);
                    });
                    $("#item_add").modal("show");
                }
            });
            return false;
        });
    });
</script>

<?php include_once 'footer.php'; ?>