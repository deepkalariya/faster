</div>

<!-- Footer -->

<footer id="footer" class="py-3 bg-light">

    <div class="container">

        <p class="m-0 text-center">Copyright &copy; Ingenious WebTech <?php echo date("Y"); ?></p>

    </div>

    <!-- /.container -->

</footer>







<script>

    $(document).ready(function () {

        var footer = $("#footer").outerHeight();

        var header = $("#header").outerHeight();

        var w_h = $("#header").outerHeight();

        $("#wrap").css({"padding-bottom": footer + "px", "min-height":"calc(100% - " + header + "px)"});

        $("#footer").css("margin-top", "-" + footer + "px");

    });

    function myFunction() {

        // Get the snackbar DIV

        $("#snackbar").addClass("show");





        // After 3 seconds, remove the show class from DIV

        setTimeout(function () {

            $("#snackbar").removeClass("show");

        }, 3000);

    }

</script>

<?php 

    echo "<div id=\"snackbar\"></div>";

if(isset( $_SESSION['message']) ) {

    echo "<script>$(\"#snackbar\").removeClass(\"error\").html('{$_SESSION['message']}');myFunction();</script>";

    unset( $_SESSION['message']);

}

?>

</body>



</html>

