<?php
include_once 'connection.php';
include_once 'auth.php';

if (isset($_POST) && count($_POST) && isset($_POST['action']) && $_POST['action'] == 'save_pdf') {
    $trhtml = "";
    $index = 1;
    $total = $invoice_number = 0;
    $stmt = $db1->prepare("SELECT invoice_number FROM parties ORDER BY id DESC LIMIT 0, 1");
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute();
    $row = $stmt->fetch();
    $invoice_number = is_null($row["invoice_number"]) ? 0 : $row["invoice_number"] + 1;
    if (empty($invoice_number)) {
        $stmt = $db1->prepare("SELECT * FROM options WHERE parameter=:parameter");
        $stmt->setFetchMode(PDO::FETCH_ASSOC);
        $stmt->execute(array(":parameter" => "start_inv_no"));
        $row = $stmt->fetch();
        $invoice_number = empty($row["value"]) ? 0 : $row["value"];
    }

    $items = array();
    if (isset($_POST['item_name']) && count($_POST['item_name'])) {
        $qty_total = 0;
        $stmt = $db1->prepare("SELECT * FROM clients WHERE id=:id");
        $stmt->execute(array(":id" => $_POST['party_name']));
        $row = $stmt->fetch();
        $party_name = $row['name'];
        $margins = unserialize($row['margin']);
        foreach ($_POST['item_name'] as $key => $value) {
            $stmt = $db1->prepare("SELECT * FROM `items` WHERE id=:id");
            $stmt->setFetchMode(PDO::FETCH_ASSOC);
            $stmt->execute(array(":id" => $value));
            $row = $stmt->fetch();
            $qty = $_POST['qty'][$key];
            $new_rate = $row['rate'] + $margins[$value];
            $amount = $qty * $new_rate;
            $total += $amount;
            $qty_total += $qty;
            $sql = "INSERT INTO sale_items (item_id, client_id, inv_id, qty, sale_date) VALUES (:item_id, :client_id, :inv_id, :qty, :sale_date)";
            $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
            $stmt = $db1->prepare($sql);
            $stmt->execute(array(":item_id" => $value, ":client_id" =>$_POST['party_name'], ":inv_id" => $invoice_number, ":qty" => $qty, ":sale_date" => date('Y-m-d H:i:s')));
            update_pump_qty($value);
            $items[] = array("name" => $row['name'], "rate" => $new_rate, "qty" => $qty);
            $trhtml .= "<tr class='details bottom-line'>";
            $trhtml .= "<td class='right left'>{$index}</td>";
            $trhtml .= "<td class='right'>{$row['name']}</td>";
            $trhtml .= "<td class='right' align='right'>" . number_format($new_rate, 2) . "</td>";
            $trhtml .= "<td class='right' align='right'>{$qty}</td>";
            $trhtml .= "<td class='right' align='right'>" . number_format($amount, 2) . "</td>";
            $trhtml .= "</tr>";
            $index++;
        }
    }
    $serialize = serialize($items);

    $invoice_date = $_POST['invoice_date'];

    $sql = "INSERT INTO parties (party_name, invoice_number, items, total) VALUES (:party_name, :invoice_number, :items, :total)";
    $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
    $stmt = $db1->prepare($sql);
    $stmt->execute(array(":party_name" => $_POST['party_name'], ":invoice_number" => $invoice_number, ":items" => $serialize, ":total" => $total));
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

    require("./pdf/html2pdf.class.php");

    $pdf = new HTML2PDF('P', 'A4', 'en', true, 'UTF-8', array(15, 10, 5, 10));
    $pdf->setDefaultFont('Arial');
    $pdf->writeHTML($content);
    $pdf->Output($file_name, 'FD', $fullPath);

    /*   if (file_exists($fullPath)) {
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
    <form method="post" id="invoice_form" action="">
        <div class="row">
            <div class="col-sm-6">
                <h3 class="mt-4">
                    <i class="fa fa-gear"></i>
                    Invoice
                </h3>
            </div>
            <div class="col-sm-6 mt-4 text-right">
                <button class="btn btn-danger" id="generate_pdf" title="Generate PDF"><i class="fa fa-file-pdf-o"></i></button>
                <a id="additem" style="margin-right: 5px;" class="btn btn-success" href="#" title="Add item"><i class="fa fa-plus"></i></a>
            </div>
        </div>
        <hr/>
        <div class="row">
            <div class="col-sm-12">
                <div class="row new">
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="col-xs-12 input-group">
                                <select name="party_name" id="party_name" class="form-control">
                                    <option value="">Select Client</option>
                                    <?php
                                    $stmt = $db1->prepare("SELECT * FROM `clients` order by name");
                                    $stmt->setFetchMode(PDO::FETCH_ASSOC);
                                    $stmt->execute();
                                    while ($row = $stmt->fetch()) {
                                        ?>
                                        <option value="<?php echo $row['id']; ?>"><?php echo $row['name']; ?></option>
                                        <?php
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="form-group">
                            <div class="col-xs-12 input-group">
                                <input type="text" name="invoice_date" value="<?php echo date("d/m/Y"); ?>" class="form-control" placeholder="DD/MM/YYYY">
                            </div>
                        </div>
                    </div>

                </div>
                <input type="hidden" name="action" value="save_pdf">
                <div class="row">
                    <div class="col-sm-1">#</div>
                    <div class="col-sm-4">Description</div>
                    <div class="col-sm-2 text-right">Rate (in <?php echo $currency; ?>)</div>
                    <div class="col-sm-2">Quantity</div>
                    <div class="col-sm-2 text-right">Amount (in <?php echo $currency; ?>)</div>
                    <div class="col-sm-1"></div>
                </div>
                <hr/>
                <div id="item_wrapper">
                </div>
                <hr/>
                <div class="row">
                    <div class="col-sm-1"></div>
                    <div class="col-sm-4">Total</div>
                    <div class="col-sm-2 text-right"></div>
                    <div id="grand_total_qty" class="col-sm-2 text-right">0</div>
                    <div id="grand_total" class="col-sm-2 text-right"><?php echo $currency; ?> 0.00</div>
                    <div class="col-sm-1"></div>
                </div>

            </div>
        </div>
    </form>
</div>
<div id="itemtemplate" style="display:none;">
    <div class="row new">
        <div class="col-sm-1"></div>
        <div class="col-sm-4">
            <div class="form-group">
                <div class="col-xs-12 input-group">
                    <select name="item_name[]" class='form-control' style="width: 100%;">
                        <option value="">Select Item</option>
                        <?php
                        $stmt = $db1->prepare("SELECT * FROM `items`");
                        $stmt->setFetchMode(PDO::FETCH_ASSOC);
                        $stmt->execute();
                        if ($stmt->rowCount()) {
                            while ($row = $stmt->fetch()) {
                                ?>
                                <option value="<?php echo $row['id']; ?>" data-rate="<?php echo $row['rate']; ?>"><?php echo $row['name']; ?></option>
                                <?php
                            }
                        }
                        ?>
                    </select>
                </div>
            </div>
        </div>
        <div class="col-sm-2 text-right"></div>
        <div class="col-sm-2">
            <div class="form-group">
                <div class="col-xs-12 input-group">
                    <input type="number" name="qty[]" value="1" class="form-control" min="1">
                </div>
            </div>
        </div>
        <div class="col-sm-2 text-right"></div>
        <div class="col-sm-1">
            <a href="#" class="btn btn-danger delete_row text-white" >
                <i class="fa fa-trash"></i>
            </a>
        </div>
    </div>
</div>



<script>
    var margins = [];
    $(document).ready(function () {
        $("#additem").click(function () {
            if ($("#party_name").val() != "") {
                $("#party_name").removeAttr("style");
                var template = $('#itemtemplate').html();
                $('#item_wrapper').append(template);

                $('#item_wrapper').find("> div.row").each(function () {
                    var row = $(this);
                    calculate_total();
                    if ($(this).hasClass("new")) {

                        var rate = $(this).find('select option:selected', this).attr('data-rate');
                        var pump_id = $(this).find('select option:selected', this).val();
                        var profit = margins[pump_id];
                        var new_rate = parseFloat(rate) + parseFloat(profit)
                        $(this).find(" > div:nth-child(3)").html(new_rate);
                        $(this).find('select').change(function () {
                            var rate = $('option:selected', this).attr('data-rate');
                            var pump_id = $(this).val();
                            var profit = margins[pump_id];
                            var new_rate = parseFloat(rate) + parseFloat(profit);
                            $(row).find(" > div:nth-child(3)").html(numberWithCommas(new_rate));
                            calculate_total();
                        });

                        $(this).find('input').on("change blur", function () {
                            calculate_total();
                        });

                        $(this).find(".delete_row").click(function () {
                            $(this).parent().parent().remove();
                            calculate_total();
                            return false;
                        });

                        $(this).removeClass("new");
                    }
                });
            } else {
                $("#party_name").css("color", "red").focus();
            }
            return false;
        });

        $("#party_name").change(function () {
            if ($(this).val() != "") {
                var id = $(this).val();
                $("#party_name").removeAttr("style");
                $.ajax({
                    url: "clients.php",
                    type: "post",
                    data: "action=edit&id=" + id,
                    dataType: "json",
                    success: function (res) {
                        margins = [];
                        $.each(res.margins, function (key, val) {
                            margins[key] = parseFloat(val);
                        });
                        $('#item_wrapper').find("> div.row").each(function () {
                            var rate = parseFloat($(this).find('select option:selected', this).attr('data-rate'));
                            var pump_id = $(this).find('select option:selected', this).val();
                            var profit = margins[pump_id];
                            var new_rate = parseFloat(rate) + parseFloat(profit)
                            $(this).find(" > div:nth-child(3)").html(numberWithCommas(new_rate));
                        });
                        calculate_total()
                    }
                });
            } else {
                $("#party_name").css("color", "red");
            }
        });

        $("#generate_pdf").click(function () {
            if ($("#party_name").val().split(" ").join("") == "") {
                $("#snackbar").addClass("error");
                $("#snackbar").html("Party Name must be selected.");
                myFunction();
                return false;
            } else if ($("#invoice_form").find("select").length) {
                $("#invoice_form").submit();
            } else {
                $("#snackbar").addClass("error");
                $("#snackbar").html("Atleast 1 item must be added.");
                myFunction();
                return false;
            }
        });

    });

    function calculate_total() {
        var index = 1;
        var grand_total = 0;
        var grand_total_qty = 0;
        $('#item_wrapper').find("> div.row").each(function () {
            var row = $(this);
            $(this).find(" > div:first-child").html(index);
            index++;

            var rate = parseFloat($(this).find('select option:selected', this).attr('data-rate'));
            var pump_id = $(this).find('select option:selected', this).val();
            var profit = margins[pump_id];
            var new_rate = parseFloat(rate) + parseFloat(profit);
            var qty = parseFloat($(this).find(' > div:nth-child(4) input').val());
            var sub_total = new_rate * qty;
            grand_total += sub_total;
            grand_total_qty += qty;
            $(this).find(" > div:nth-child(5)").html(numberWithCommas(sub_total.toFixed(2)));
        });
        $("#grand_total").html("<?php echo $currency; ?> " + numberWithCommas(grand_total.toFixed(2)));
        $("#grand_total_qty").html(grand_total_qty);
    }

    function numberWithCommas(x) {
        var parts = x.toString().split(".");
        parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
        return parts.join(".");
    }

</script>
<?php include_once 'footer.php'; ?>