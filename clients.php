<?php
include_once 'connection.php';
include_once 'auth.php';

if (count($_POST) && isset($_POST['submititemform'])) { //echo $_POST['item_name']; die();
    if ($_POST["client_id"] > 0) {
        $sql = "UPDATE clients SET name=:name, city=:city, contact=:contact, margin=:margin WHERE id=:id";
        $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $db1->prepare($sql);
        $stmt->execute(array(":name" => $_POST['client_name'], ":city" => $_POST['client_city'], ":contact" => $_POST['client_contact'], ":margin" => serialize($_POST['margin']), ":id" => $_POST["client_id"]));
    } else {
        $sql = "INSERT INTO clients (name, city, contact, margin) VALUES (:name, :city, :contact, :margin)";
        $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $db1->prepare($sql);
        $stmt->execute(array(":name" => $_POST['client_name'], ":city" => $_POST['client_city'], ":contact" => $_POST['client_contact'], ":margin" => serialize($_POST['margin'])));
    }
    $_SESSION['message'] = '<i class="fa fa-save"></i> Client saved successfully.';
    session_write_close();
    header("location: clients.php");
    die;
}

if (isset($_GET['action']) && $_GET['action'] == 'delete' && $_GET['id'] > 0) {
    $stmt = $db1->prepare("DELETE FROM items WHERE id=:id");
    if ($stmt->execute(array(":id" => $_GET['id']))) {
        $_SESSION['message'] = '<i class="fa fa-trash"></i> Item is deleted successfully.';
    }
    session_write_close();
    header("location: settings.php");
    die;
}
if (isset($_POST['action']) && $_POST['action'] == 'edit' && $_POST['id'] > 0) {
    $stmt = $db1->prepare("SELECT * FROM clients WHERE id=:id");
    $stmt->execute(array(":id" => $_POST['id']));
    $row = $stmt->fetch();
    $res['id'] = $row['id'];
    $res['name'] = $row['name'];
    $res['city'] = $row['city'];
    $res['contact'] = $row['contact'];

    $res['margins'] = unserialize($row['margin']);
    echo json_encode($res);
    die;
}

include_once 'header.php';
?>
<div class="container">
    <div class="row">
        <div class="col-sm-12">
            <h3 class="mt-4">
                <i class="fa fa-gear"></i>
                Clients
                <a class="btn btn-success pull-right" href="" title="Add Client" id="new_client"><i class="fa fa-plus"></i></a>
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
                                    <th> Name </th>
                                    <th> City </th>
                                    <th> Contact </th>
                                    <th> Action </th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $stmt = $db1->prepare("SELECT * FROM `clients`");
                                $stmt->setFetchMode(PDO::FETCH_ASSOC);
                                $stmt->execute();
                                $index = 0;
                                if ($stmt->rowCount()) {
                                    while ($row = $stmt->fetch()) {
                                        $index++;
                                        ?>
                                        <tr class="new_row">
                                            <td><?php echo $index; ?></td>
                                            <td><?php echo $row['name']; ?></td>
                                            <td><?php echo $row['city']; ?></td>
                                            <td><?php echo $row['contact']; ?></td>
                                            <td>
                                                <a href="<?php echo $row["id"]; ?>" class="btn btn-success edit_row text-white" style="margin-left: 25px;">
                                                    <i class="fa fa-edit"></i>
                                                </a>
                                                <a href="#" class="btn btn-danger delete_row text-white" style="margin-left: 25px;" data-id="<?php echo $row["id"]; ?>" >
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

<div id="client_add" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <form action="" method="POST" id="client_form"  class='form-horizontal form-validate'>
                <input type="hidden" name="client_id" id="client_id">
                <div class="modal-header modal-header-info">
                    <h4 class="modal-title">Add Client</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="row">
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="client_name" class="control-label col-sm-12">Name</label>
                                <div class="col-sm-12">
                                    <input type="text" name="client_name" id="client_name"  required="true" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="client_city" class="control-label col-sm-12">City</label>
                                <div class="col-sm-12">
                                    <input type="text" name="client_city" id="client_city"  required="true" class="form-control">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="client_contact" class="control-label col-sm-12">Contact</label>
                                <div class="col-sm-12">
                                    <input type="text" name="client_contact" id="client_contact"  required="true" class="form-control">
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-6">
                            <div class="form-group">
                                <label for="client_name" class="control-label col-sm-12"><strong>Set Margin</strong></label>
                                <div class="col-sm-12">
                                    <?php
                                    $stmt = $db1->prepare("SELECT * FROM `items`");
                                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                                    $stmt->execute();
                                    while ($row = $stmt->fetch()) {
                                        ?>
                                        <div class="row">
                                            <label class="control-label col-xs-6 col-sm-6 col-md-6 col-lg-6"><?php echo $row['name']; ?></label>
                                            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6">
                                                <input type="number" step="any" maxlength="10" name="margin[<?php echo $row['id']; ?>]" id="margin_<?php echo $row['id']; ?>" required="true" class="form-control">
                                            </div>
                                        </div>
                                        <?php
                                    }
                                    ?>                                
                                </div>
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

<div id="delete-modal" class="modal fade" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header modal-header-danger">
                <h3 class="modal-title">Delete Confirmation</h3>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <!-- /.modal-header -->
            <div class="modal-body">
                <p style="color: #d9534f;">If you Delete, You must be lost billing details for this client.</p>
                <p>Are You Sure want to delete "<span></span>"?</p>
            </div>
            <!-- /.modal-body -->
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">No</button>
                <a href="" class="btn btn-danger">Yes</a>
            </div>
            <!-- /.modal-footer -->
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<!-- /#modal-1.modal fade -->

<div id="delete-confirmation-modal" class="modal fade" role="dialog" data-backdrop="static">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Delete Confirmation</h3>
            </div>
            <!-- /.modal-header -->
            <div class="modal-body">
                <h4>This item will be deleted. Are You Sure?</h4>
            </div>
            <!-- /.modal-body -->
            <div class="modal-footer">
                <!-- <button type="button" id="sale_modal_close" class="btn btn-default" data-dismiss="modal" >No</button> -->
                <button type="button" class="btn btn-default" id="delete_modal_close">No</button>
                <a href="" id="delete_confirm" class="btn btn-danger">Yes</a>
            </div>
            <!-- /.modal-footer -->
        </div>
        <!-- /.modal-content -->
    </div>
    <!-- /.modal-dialog -->
</div>
<script type="text/javascript">
    $(document).ready(function () {
        // var del_obj = null;
        $("#new_client").click(function () {
            $("#client_add #item_name").val("");
            $("#client_add #item_rate").val("");
            $("#client_add #item_id").val(0);
            $("#client_add").modal("show");
            return false;
        });

        /*    $('#delete_modal_close').click(function () {
         del_obj = null;
         $('#delete-confirmation-modal').modal('hide');
         });
         
         $("#delete_confirm").click(function () {
         $(del_obj).parent().parent().remove();
         $("#affliate_level_form").trigger("submit");
         del_obj = null;
         $('#delete-confirmation-modal').modal('hide');
         return false;
         }); */

        $(".delete_row").click(function () {
            var title = $(this).parent().parent().find("td:nth-child(2)").html();
            var id = $(this).attr("data-id");
            $('#delete-modal').find(".modal-body span").html(title);
            $('#delete-modal').find(".modal-footer a").attr("href", "settings.php?action=delete&id=" + id);
            $('#delete-modal').modal('show');
            return false;
        });

    });

    $(".edit_row").click(function () {
        var id = $(this).attr("href");
        //$("#quickpost_id").val(id);
        $.ajax({
            url: "clients.php",
            type: "post",
            data: "action=edit&id=" + id,
            dataType: "json",
            success: function (res) {
                $("#client_add #client_name").val(res.name);
                $("#client_add #client_city").val(res.city);
                $("#client_add #client_contact").val(res.contact);
                $("#client_add #client_id").val(res.id);

                $.each(res.margins, function (key, val) {
                    $("#margin_" + key).val(val);
                });

                $("#client_add").modal("show");
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
</script>
<?php include_once 'footer.php'; ?>