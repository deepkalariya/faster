<?php
include_once 'connection.php';
include_once 'auth.php';

if (isset($_GET["id"])) {
    $stmt = $db1->prepare("SELECT * FROM parties WHERE id=:id");
    $stmt->setFetchMode(PDO::FETCH_ASSOC);
    $stmt->execute(array(":id" => $_GET["id"]));
    $row = $stmt->fetch();
    $items = unserialize($row["items"]);
} else {
    header('Location: view_invoice_list.php');
    die;
}

include_once 'header.php';
?>
<div class="container">
    <div class="row">
        <div class="col-sm-4"> Party Name : <?php echo $row["party_name"]; ?></div>
        <div class="col-sm-4"> Invoice Number : <?php echo $row["invoice_number"]; ?></div>
        <div class="col-sm-4"> Date : <?php echo date("d/m/Y", strtotime($row['invoice_date'])); ?></div>
    </div>
    <div class="row"> 
        <div class="col-sm-1"><b> # </b></div>
        <div class="col-sm-3"><b> Item Name </b></div>
        <div class="col-sm-3 text-right"><b>Rate (in <?php echo $currency; ?>) </b></div>
        <div class="col-sm-2"><b>Quantity </b></div>
        <div class="col-sm-3"><b> Amount (in <?php echo $currency; ?>)</b></div>
    </div>
    <?php foreach ($items as $key => $value) {
        ?>
        <div class="row">
            <div class="col-sm-1"> <?php echo $key + 1; ?></div>
            <div class="col-sm-3"> <?php echo $value["name"]; ?></div>
            <div class="col-sm-3 text-right"> <?php echo $value["rate"]; ?></div>
            <div class="col-sm-2"> <?php echo $value["qty"]; ?></div>
            <div class="col-sm-3"> <?php echo $value["qty"] * $value["rate"]; ?></div> 
        </div>
    <?php } ?>
    <div class="row"> 
        <div class="col-sm-9 text-right"><b> Total </b></div>
        <div class="col-sm-3"><b> <?php echo $row["total"]; ?> </b></div>
    </div>
</div>



    <script type="text/javascript">
        $(document).ready(function () {


        });
    </script>
    <?php include_once 'footer.php'; ?>