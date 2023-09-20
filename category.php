<?php 
include_once 'connection.php';

if(isset($_POST['action']) && $_POST['action'] == 'add'){
	$stmt=$db1->prepare("INSERT INTO stock_category (cat_name) VALUES (:cat_name)");
	$stmt->execute(array(":cat_name" => $_POST['category_name']));
	$_SESSION['message'] = '<i class="fa fa-save"></i> Category Add successfully.';
	session_write_close();
	echo json_encode(array("status"=>"ok","Message"=>"Category Add Successfully"));
	die;
}

if (isset($_POST['action']) && $_POST['action'] == 'edit' && $_POST['id'] > 0) {
    $stmt = $db1->prepare("SELECT * FROM stock_category WHERE id=:id");
    $stmt->execute(array(":id" => $_POST['id']));
    $row = $stmt->fetch();
    echo json_encode($row);
    die;
}

if(isset($_POST['action']) && $_POST['action'] == 'update'){
    $stmt = $db1->prepare("UPDATE stock_category SET cat_name=:cat_name WHERE id=:id");
    $stmt->execute(array(":cat_name" => $_POST['category_name'], ":id" => $_POST["category_id"]));
    $_SESSION['message'] = '<i class="fa fa-save"></i> Category Update Successfully.';
    session_write_close();
    echo json_encode(array("status" => "ok", "Message" => "Category Update Successfully"));
    die;
}

if(isset($_GET['action']) && $_GET['action'] == 'delete'){
    $stmt = $db1->prepare("SELECT * FROM total_stock WHERE cat_id=:cat_id");
    $stmt->execute(array(":cat_id" => $_GET['id']));
    if($stmt->rowCount()){
        echo json_encode(array("status" => "error", "Message" => "This Category is Already Used"));
    }else{
        $stmt = $db1->prepare("DELETE FROM stock_category WHERE id=:id");
        $stmt->execute(array(":id" => $_GET['id']));
        echo json_encode(array("status" => "ok", "Message" => "Category Deleted Successfully"));
    }
    die;
}

include_once 'header.php';
?>

<div class="container">
    <div class="row">
        <div class="col-sm-11">
            <h3 class="mt-4">
                <i class="fa fa-gear"></i>
                Category
            </h3>
        </div>
        <div class="col-sm-1 mt-4">
            <a class="btn btn-success pull-right" href="" title="Catrgory" id="new_category"><i class="fa fa-plus"></i></a>
        </div>
    </div>
    <div class="row">
        <div class="col-sm-12">
            <div class="box box-color box-bordered">
                <div class="box-content nopadding">
                	<table id="item_options" class="table table-hover table-nomargin item_table">
                	    <thead>
                	        <tr>
                	            <th> # </th>
                	            <th> Category Name </th>
                	            <th> Action </th>
                	        </tr>
                	    </thead>
                	    <tbody>
                	        <?php
                	        $sql = "SELECT * FROM `stock_category`";
                	        $stmt = $db1->prepare($sql);
                	        $stmt->setFetchMode(PDO::FETCH_ASSOC);
                	        $stmt->execute();
                	        $index = 0;
                	        if ($stmt->rowCount()) {
                	            while ($row = $stmt->fetch()) {
                	                $index++;
                	                ?>
                	                <tr class="new_row">
                	                    <td><?php echo $index; ?>
                	                    </td>
                	                    <td>
											<?php echo $row['cat_name']; ?> 
											<a href="stocks.php?q=<?php echo $row['id']; ?>" class="btn btn-success btn-sm"><i class="fa fa-eye"></i></a>
                	                    </td>
                	                    <td>
                	                        <a href="<?php echo $row["id"]; ?>" class="btn btn-success edit_row text-white">
                	                            <i class="fa fa-edit"></i>
                	                        </a> 
                	                        <a href="#" class="btn btn-danger delete_row text-white" data-id="<?php echo $row["id"]; ?>" >
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
                </div>
            </div>
        </div>
    </div>
</div>

<div id="category_add" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <form action="" method="POST" id="add_category"  class='form-horizontal form-validate'>
                <input type="hidden" name="category_id" id="category_id">
                <input type="hidden" name="action" id="action" value="">
                <div class="modal-header modal-header-primary">
                    <h4 class="modal-title">Category</h4>
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
                </div>
                <div class="modal-body" style="max-height: 400px; overflow-y: auto;">
                    <div class="form-group row">
                        <label for="category_name" class="control-label col-sm-3">Category Name</label>
                        <div class="col-sm-9">
                            <input type="text" name="category_name" id="category_name" required="true" class="form-control">
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
	$(document).ready(function(){
		$("#new_category").click(function () {
            $("#category_add #category_name").val("");
            $("#category_add #action").val("add");
            $("#category_add #category_id").val(0);
            $("#category_add").modal("show");
            return false;
        });

        $('#submititemform').click(function(){
        	var formData=$('#add_category').serialize();
        	$.ajax({
        		url: "category.php",
        		type: "post",
        		data: formData,
        		dataType: "json",
        		success:function(res){
        			if(res.status == "ok"){
                        window.location.href = 'category.php';
                    }
        		}
        	})
        	return false;
        });

        $(".edit_row").click(function () {
        var id = $(this).attr("href");
        $.ajax({
            url: "category.php",
            type: "post",
            data: "action=edit&id=" + id,
            dataType: "json",
            success: function (res) {
                $("#category_add #category_name").val(res.cat_name);
                $("#category_add #action").val("update");
                $("#category_add #category_id").val(res.id);
                $("#category_add").modal("show");
            }
        });
        return false;
        });

        $('.delete_row').click(function(){
            var obj = $(this);
            var del_id = $(this).data('id');
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
                        url: "category.php",
                        type: "get",
                        data: "id="+del_id+"&action=delete",
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
                                    'This Category is Already Used.',
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
</script>

<?php include_once 'footer.php';?>