<?php

include_once 'connection.php';

include_once 'auth.php';

include_once 'header.php';

?>

<div class="container">

    <div class="row">

        <div class="col-sm-6">

            <h3 class="mt-4">

                <i class="fa fa-list"></i>

                Pumps Sales

            </h3>

        </div>

        <div class="col-sm-6">

            <div style="margin: 24px 8px;">

                <form action="" method="post">

                    <select name="pumps" class="form-control" style="width: auto; display: inline-block;">

                        <option value="">All</option>

                        <?php

                            $stmt = $db1->prepare("SELECT * FROM `items`");

                            $stmt->setFetchMode(PDO::FETCH_ASSOC);

                            $stmt->execute();

                            while ($row = $stmt->fetch()) {

                                echo "<option value=\"{$row['name']}\" " . ((isset($_POST['pumps']) && $_POST['pumps'] == $row['name']) ? 'selected' : '') . ">{$row['name']}</option>";

                            }

                        ?>

                    </select>

                    <select name="month" class="form-control" style="width: auto; display: inline-block;">

                        <option value="">Month</option>

                        <?php

                        for ($i = 1; $i <= 12; $i++) {

                            $m = $i;

                            if ($i < 10) {

                                $m = "0" . $i;

                            }

                            echo "<option value='{$m}' " . ((isset($_POST['month']) && $_POST['month'] == $m) ? 'selected' : '') . ">{$m}</option>";

                        }

                        ?>

                    </select>

                    <select name="year" class="form-control" style="width: auto; display: inline-block;">

                        <option value="">Year</option>

                        <?php

                        for ($i = date("Y"); $i >= 2021; $i--) {

                            echo "<option value='{$i}' " . ((isset($_POST['year']) && $_POST['year'] == $i) ? 'selected' : '') . ">{$i}</option>";

                        }

                        ?>

                    </select>

                    <button type="submit" class="btn btn-success" name="search" title="search"><i class="fa fa-search"></i></button>

                </form>

            </div>

        </div>

    </div>

    <div class="row">

        <div class="col-sm-12">

            <div class="box box-color box-bordered">

                <div class="box-content nopadding">

                    <table class="table table-hover table-nomargin item_table" id="res_table">

                        <thead>

                            <tr>

                                <th> </th>

                                <th> Invoice Number </th>

                                <th> Party Name </th>

                                <th> Invoice Date </th>

                                <th> Total </th>

                            </tr>

                        </thead>

                        <tbody>

                            <?php

                            $qry = "SELECT p.*, c.name FROM `parties` p LEFT JOIN `clients` c ON c.id = p.party_name WHERE 1";

                            $param = array();

                            if (isset($_POST['pumps']) && $_POST['pumps'] != "") {

                                $qry .= " AND p.items LIKE :query";

                                $param[':query'] = '%' . $_POST['pumps'] . '%';

                            }

                            if (isset($_POST['month']) && $_POST['month'] != "") {

                                $qry .= " AND MONTH(p.invoice_date)=:month";

                                $param[':month'] = $_POST['month'];

                            }

                            if (isset($_POST['year']) && $_POST['year'] != "") {

                                $qry .= " AND YEAR(p.invoice_date)=:year";

                                $param[':year'] = $_POST['year'];

                            }

                            $stmt = $db1->prepare($qry);

                            $stmt->setFetchMode(PDO::FETCH_ASSOC);

                            $stmt->execute($param);

                            $index = 0;

                            if ($stmt->rowCount()) {

                                while ($row = $stmt->fetch()) {

                                    ?>

                                    <tr class="new_row">

                                        <td> </td>

                                        <td> <?php echo $row["invoice_number"]; ?>

                                        </td>

                                        <td> <?php echo $row['name']; ?>

                                        </td>

                                        <td><?php echo date("d/m/Y", strtotime($row['invoice_date'])); ?>

                                        </td>

                                        <td><?php echo $row['total']; ?>

                                        </td>

                                    </tr>

                                    <?php

                                }

                            }

                            ?>

                        </tbody>

                    </table>

                    <form method="post" id="invoice_form" action="">

                        <input type="hidden" name="action" value="save_pdf">

                        <input type="hidden" name="pdf_id" id="pdf_id" value="">

                    </form>

                </div>

            </div>

        </div>

    </div>

</div>

<div id="loading_modal" class="modal fade" role="dialog" data-backdrop="static" data-keyboard="false">

    <div class="modal-dialog">

        <div class="modal-content">

            <div class="modal-header">

                <h3 class="modal-title">Processing...</h3>

            </div>

            <!-- /.modal-header -->

            <div class="modal-body">

                <div class="text-center"><img src="images/ajax-loader-small.gif"/></div>

            </div>

            <!-- /.modal-body -->

            <div class="modal-footer">

                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>

            </div>

            <!-- /.modal-footer -->

        </div>

        <!-- /.modal-content -->

    </div>

    <!-- /.modal-dialog -->

</div>



<div id="overlay" style="display:none;">

    <div class="spinner"></div>

    <br/>

    Loading...

</div>


<style type="text/css"> 

    #overlay {

        background: #ffffff;

        color: #666666;

        position: fixed;

        height: 100%;

        width: 100%;

        z-index: 5000;

        top: 0;

        left: 0;

        float: left;

        text-align: center;

        padding-top: 10%;

        opacity: .80;

    }

    .spinner {

        margin: 0 auto;

        height: 64px;

        width: 64px;

        animation: rotate 0.8s infinite linear;

        border: 5px solid firebrick;

        border-right-color: transparent;

        border-radius: 50%;

    }

    @keyframes rotate {

        0% {

            transform: rotate(0deg);

        }

        100% {

            transform: rotate(360deg);

        }

    }



</style>



<link rel="stylesheet" type="text/css" href="plugins/datatables/jquery.dataTables.min.css"/>

<link rel="stylesheet" type="text/css" href="plugins/datatables/responsive.dataTables.min.css"/>

<script type="text/javascript" src="plugins/datatables/jquery.dataTables.min.js"></script>

<script type="text/javascript" src="plugins/datatables/dataTables.responsive.min.js"></script>

<!--<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.10.23/css/jquery.dataTables.min.css"/>

<link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/responsive/2.2.6/css/responsive.dataTables.min.css"/>



<script type="text/javascript" src="https://code.jquery.com/jquery-3.5.1.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/1.10.23/js/jquery.dataTables.min.js"></script>

<script type="text/javascript" src="https://cdn.datatables.net/responsive/2.2.6/js/dataTables.responsive.min.js"></script>-->

<script type="text/javascript">

    $(document).ready(function () {

        $('#res_table').DataTable({

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