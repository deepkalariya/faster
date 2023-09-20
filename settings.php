<?php
include_once 'connection.php';
include_once 'auth.php';

if (count($_POST) && isset($_POST['inv_submit'])) {
    $inv_start_no = $_POST['invoice_id'];
    $stmt = $db1->prepare("SELECT * FROM options WHERE parameter=:parameter");
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array(":parameter" => "start_inv_no"));
    if ($stmt->rowCount()) {
        $row = $stmt->fetch();
        $sql = "UPDATE options SET value=:value WHERE id=:id";
        $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $db1->prepare($sql);
        $stmt->execute(array(":value" => $inv_start_no, ":id" => $row["id"]));
    } else {
        $sql = "INSERT INTO options (parameter, value) VALUES (:parameter, :value)";
        $db1->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
        $stmt = $db1->prepare($sql);
        $stmt->execute(array(":parameter" => "start_inv_no", ":value" => $inv_start_no));
    }
    $_SESSION['message'] = '<i class="fa fa-save"></i> Invoice start number saved successfully.';
    session_write_close();
    header("location: settings.php");
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
        <div class="col-sm-12">
            <form id="invoice_form" action="" method="post">
                <div class="input-group" style="padding-top: 15px;">
                    <label style="margin-right: 15px;">Invoice start number : </label>
                    <input name="invoice_id" value="<?php echo $invoice_number; ?>" type="text" class="form-control" required="required">
                    <div class="input-group-btn">
                        <button class="btn btn-success" name="inv_submit" type="submit" style="margin-left: 15px;">Save</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<?php include_once 'footer.php'; ?>