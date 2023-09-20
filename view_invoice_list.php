<?php
include_once 'connection.php';
include_once 'auth.php';

//print_r($_POST);die;
if (isset($_POST['download_all_pdf'])) {
    $pdfs = glob("invoice/{$_POST['year']}/{$_POST['month']}/*.{pdf}", GLOB_BRACE);
    $files = array();

# create new zip opbject
    $zip = new ZipArchive();

# create a temp file & open it
    $tmp_file = tempnam('tmp/download', '');
    $zip->open($tmp_file, ZipArchive::CREATE);

# loop through each file
    foreach ($pdfs as $file) {

        # download file
        $download_file = file_get_contents($file);

        #add it to the zip
        $zip->addFromString(basename($file), $download_file);
    }

# close zip
    $zip->close();

# send the file to the browser as a download
    header('Content-disposition: attachment; filename=download-' . $_POST['year'] . '-' . $_POST['month'] . '.zip');
    header('Content-type: application/zip');
    readfile($tmp_file);
}
/*$stmt = $db1->prepare("SELECT * FROM clients WHERE id=:id");
$stmt->execute(array(":id" => $_POST['id']));
$row = $stmt->fetch();
$margins = unserialize($row['margin']);*/
if (isset($_POST['action']) && $_POST['action'] == 'view' && $_POST['id'] > 0) {
    $stmt = $db1->prepare("SELECT p.*, c.name FROM parties p LEFT JOIN clients c on c.id=p.party_name WHERE p.id=:id");
    $stmt->execute(array(":id" => $_POST['id']));
    $row = $stmt->fetch();
    $items = unserialize($row["items"]);
    $date = date("d/m/Y", strtotime($row["invoice_date"]));
    $total_qty = 0;

    $html = "
    <div class=\"container\">
    <div class=\"row\">
        <div class=\"col-sm-4\"> <strong>Party Name :</strong> {$row["name"]} </div>
        <div class=\"col-sm-4 text-center\"> <strong>Invoice Number :</strong> {$row["invoice_number"]} </div>
        <div class=\"col-sm-4 text-right\"> <strong>Date :</strong> {$date} </div>
    </div>
    <div class=\"row\"> <div class=\"col-sm-12\"> <hr/> </div> </div>
    <div class=\"row\"> 
        <div class=\"col-sm-1\"><b> # </b></div>
        <div class=\"col-sm-3\"><b> Item Name </b></div>
        <div class=\"col-sm-3 text-right\"><b>Rate (in {$currency}) </b></div>
        <div class=\"col-sm-2 text-right\"><b> Qty </b></div>
        <div class=\"col-sm-3 text-right\"><b> Amount (in {$currency})</b></div>
    </div>";
    foreach ($items as $key => $value) {
        $number = $key + 1;
        $amount = $value["qty"] * $value["rate"];
        $total_qty += $value["qty"];
        $html .= "<div class=\"row\">
            <div class=\"col-sm-1\"> {$number} </div>
            <div class=\"col-sm-3\"> {$value["name"]} </div>
            <div class=\"col-sm-3 text-right\"> {$value["rate"]} </div>
            <div class=\"col-sm-2 text-right\"> {$value["qty"]} </div>
            <div class=\"col-sm-3 text-right\"> " . number_format($amount, 2) . " </div> 
        </div>";
    }
    $html .= "<div class=\"row\"> <div class=\"col-sm-12\"> <hr/> </div> </div>
        <div class=\"row\"> 
        <div class=\"col-sm-6 text-right\"><b> Total </b></div>
        <div class=\"col-sm-3 text-right\"><b> " . number_format($total_qty) . " </b></div>
        <div class=\"col-sm-3 text-right\"><b> " . number_format($row["total"]) . " </b></div>
    </div>
</div>";

    echo json_encode(array("html" => $html));
    die;
}

if (isset($_POST) && count($_POST) && isset($_POST['action']) && $_POST['action'] == 'save_pdf') {
//    print_r($_POST);die;
    $trhtml = "";
    $total = $invoice_number = $qty_total = 0;
    $pdf_id = is_null($_POST["pdf_id"]) ? 0 : $_POST["pdf_id"];

    $stmt = $db1->prepare("SELECT p.*, c.name FROM `parties` p LEFT JOIN `clients` c ON c.id=p.party_name WHERE p.id=:id");
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array(":id" => $pdf_id));
//    $stmt->debugDumpParams();
    $row = $stmt->fetch();
    $invoice_number = $row["invoice_number"];

//    var_dump($row);die;
    $items = unserialize($row["items"]);
    if (count($items)) {
        foreach ($items as $key => $value) {
            $index = $key + 1;
            $qty = $value["qty"];
            $amount = $value["qty"] * $value["rate"];
            $total += $amount;
            $qty_total += $qty;
            $trhtml .= "<tr class='details bottom-line'>";
            $trhtml .= "<td class='right left'>{$index}</td>";
            $trhtml .= "<td class='right'>{$value["name"]}</td>";
            $trhtml .= "<td class='right' align='right'>" . number_format($value["rate"], 2) . "</td>";
            $trhtml .= "<td class='right' align='right'>{$qty}</td>";
            $trhtml .= "<td class='right' align='right'>" . number_format($amount, 2) . "</td>";
            $trhtml .= "</tr>";
        }
    }

    $party_name = $row["name"];
    $invoice_date = date("d/m/Y", strtotime($row["invoice_date"]));

    $invoice_date_year = date("Y", strtotime(str_replace("/", "-", $invoice_date)));
    $invoice_date_month = date("m", strtotime(str_replace("/", "-", $invoice_date)));
    $file_name = sanitize_name($party_name) . "_" . $invoice_number . ".pdf";
    if (!is_dir("invoice/$invoice_date_year/$invoice_date_month/")) {
        mkdir("invoice/$invoice_date_year/$invoice_date_month/", 0755, true);
    }
    $fullPath = "invoice/$invoice_date_year/$invoice_date_month";
    ob_start();
    include('./invoice-pdf.php');
    $content = ob_get_clean();
//    print_r($content);die;

    require("./pdf/html2pdf.class.php");

    $pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(15, 10, 5, 10));
    $pdf->setDefaultFont('Arial');
    $pdf->writeHTML($content);
    $pdf->Output($file_name, 'FD', $fullPath);

    /*  if (file_exists($fullPath)) {
      if ($fd = fopen($fullPath, "r")) {
      $fileSize = filesize($fullPath);
      $pathParts = pathinfo($fullPath);
      $ext = strtolower($pathParts["extension"]);

      switch ($ext) {
      case "pdf": $contentType = "application/pdf";
      break;
      default: $contentType = "application/force-download";
      break;
      }

      header("Content-type: $contentType");
      header("Content-Disposition: attachment; filename=\"" . $file_name . "\""); // use 'attachment' to force a download
      header("Content-length: $fileSize");
      header("Cache-control: private"); //use this to open files directly
      while (!feof($fd)) {
      $buffer = fread($fd, $fileSize);
      echo $buffer;
      }
      }
      fclose($fd);
      die;
      } */
}

include_once 'header.php';
?>
<div class="container">
    <div class="row">
        <div class="col-sm-6">
            <h3 class="mt-4">
                <i class="fa fa-list"></i>
                View Invoice List
            </h3>
        </div>
        <div class="col-sm-6">
            <div style="margin: 24px 8px;">
                <form action="" method="post">
                    <select name="month">
                        <?php
                        for ($i = 1; $i <= 12; $i++) {
                            $m = $i;
                            if ($i < 10) {
                                $m = "0" . $i;
                            }
                            echo "<option value='{$m}'>{$m}</option>";
                        }
                        ?>
                    </select>
                    <select name="year">
                        <?php
                        for ($i = date("Y"); $i >= 2021; $i--) {
                            echo "<option value='{$i}'>{$i}</option>";
                        }
                        ?>
                    </select>
                    <button type="submit" class="btn btn-warning" name="download_all_pdf" title="download pdf"><i class="fa fa-file-pdf-o"></i></button>
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
                                <th> Total (in Rs.)</th>
                                <th> Action </th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $stmt = $db1->prepare("SELECT p.*, c.name FROM `parties` p LEFT JOIN `clients` c ON c.id = p.party_name");
                            $stmt->setFetchMode(PDO::FETCH_ASSOC);
                            $stmt->execute();
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
                                        <td> <?php echo number_format($row['total'], 2); ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo $row["id"]; ?>" class="btn btn-success view_row text-white" style="margin-left: 25px;">
                                                <i class="fa fa-eye"></i>
                                            </a>
        <!--                                            <a href="view_invoice.php?id=<?php echo $row["id"]; ?>" class="btn btn-success text-white" style="margin-left: 25px;">
                                                <i class="fa fa-eye"></i>
                                            </a>-->
                                            <button class="btn btn-danger generate_pdf" data-id="<?php echo $row["id"]; ?>" title="Generate PDF"><i class="fa fa-file-pdf-o"></i></button>
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

<div id="view_invoice" class="modal fade" role="dialog">
    <div class="modal-dialog  modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h4 class="modal-title">View Invoice</h4>
                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">&times;</button>
            </div>
            <div class="modal-body" style="max-height: 400px; overflow-y: auto;" id="content">

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
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


        $(document).on("click", ".view_row", function () {
            var id = $(this).attr("href");
            //$("#quickpost_id").val(id);
            $.ajax({
                url: "view_invoice_list.php",
                type: "post",
                data: "action=view&id=" + id,
                dataType: "json",
                beforeSend: function () {
//                    $("#loading_modal").modal("show");
                    $('#overlay').fadeIn();
                },
                success: function (res) {
                    $('#overlay').fadeOut();
//                    $("#loading_modal").modal("hide");
//                    $("#loading_modal").find(".btn").trigger("click");
//                    $("#loading-modal").modal().hide();
                    $("#view_invoice #content").html(res.html);

                    $("#view_invoice").modal("show");
                }
            });
            return false;
        });

        $(document).on("click", ".generate_pdf", function () {
            var id = $(this).attr("data-id");
            $("#pdf_id").val(id);
            $("#invoice_form").submit();
        });

    });
</script>
<?php include_once 'footer.php'; ?>