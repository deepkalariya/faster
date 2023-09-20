<?php
include_once 'connection.php';

include_once 'header.php';

?>

<div class="container">
	<div class="row">

        <div class="col-sm-6">

            <h3 class="mt-3 mb-3">

            	<?php $qry="SELECT * FROM `total_stock` WHERE id=".$_GET['id'];

            		$stmt = $db1->prepare($qry);

            		$stmt->setFetchMode(PDO::FETCH_ASSOC);

            		$stmt->execute();

            		$row=$stmt->fetch();

            	?>

                <i class="fa fa-list"></i>

                Current Stock :- <?php echo $row['stock'];?>

            </h3>

        </div>

    </div>

    <div class="row">

        <div class="col-sm-12">

            <div class="box box-color box-bordered">

                <div class="box-content nopadding">

                    <table class="table table-hover table-nomargin item_table" id="view_stock_table">

                        <thead>

                            <tr>

                                <th> </th>

                                <th> Parts Name </th>

                                <th> In </th>

                                <th> Out </th>

                                <th> Date </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php
                            	$qry="SELECT uniontable.*, total_stock.parts_name FROM (SELECT id,parts_id,stock,add_date as entry_date,'in' as entry_type FROM `stock_in` UNION SELECT id,parts_id,stock,used_date as entry_date, 'out' as entry_type from `stock_out` ORDER BY entry_date DESC) as `uniontable` LEFT JOIN `total_stock` on total_stock.id=uniontable.parts_id WHERE total_stock.id=".$_GET['id'];

                            	$stmt = $db1->prepare($qry);

                            	$stmt->setFetchMode(PDO::FETCH_ASSOC);

                            	$stmt->execute();

                            	if($stmt->rowCount()){

                            		while($row = $stmt->fetch()){

                            			?>

                            			<tr>

                            				<td></td>

                            				<td><?php echo $row["parts_name"]; ?></td>

                            				<td><?php if($row["entry_type"] == "in"){ echo $row['stock']; } ?></td>

                            				<td><?php if($row["entry_type"] == "out"){ echo $row['stock']; } ?></td>

                            				<td><?php echo date('d-m-Y', strtotime($row["entry_date"]))?></td>

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

<link rel="stylesheet" type="text/css" href="plugins/datatables/jquery.dataTables.min.css"/>

<link rel="stylesheet" type="text/css" href="plugins/datatables/responsive.dataTables.min.css"/>



<script type="text/javascript" src="plugins/datatables/jquery.dataTables.min.js"></script>

<script type="text/javascript" src="plugins/datatables/dataTables.responsive.min.js"></script>

<script type="text/javascript">

    $(document).ready(function () {



        $('#view_stock_table').DataTable({

            responsive: {

                details: {

                    type: 'column'

                }

            },

            columnDefs: [{

                    className: 'dtr-control',

                    orderable: false,

                    targets: 0

                }],

            order: [1, 'asc']

        });



    });

</script>

<?php include_once 'footer.php'; ?>