<style type="text/css">
    <!--
    #title{
        font-size: 20px;
        color: #0679A6;
        padding-bottom: 10px;
    }
    .address{
        line-height: 20px;
    }
    .header{
        background-color: #ffedb1;
        color: #000;
        font-weight: bold;
    }
    .header th{padding: 7px 5px; border: none;}
    .details td{
        font-size: 12px;
    }
    .bottom-line{
        border-bottom: 1px solid #ffedb1;
    }
    .bottom-line td{
        padding: 7px 5px; border: none;
        border-bottom: 1px solid #ffedb1;
    }
    .total td{
        padding: 7px 5px;
    }
    .total td.border{
        border-bottom: 1px solid #ffedb1;
        border-top: 1px solid #ffedb1;
    }
    td.right{
        border-right: 1px solid #ffedb1;
    }
    td.left{
        border-left: 1px solid #ffedb1;
    }
    table.page_footer {width: 700px; border: none; background-color: #ffcc29; border-top: solid 1mm #000000; padding: 1mm; color: #000;}
    table.page_header {width: 700px; border: none; background-color: #DEEEEE; border: solid 2px #E00000; padding: 1mm;}
    .extra_fee{color: #63BBFF;}
    -->
</style>
<page style="font-size: 14px" backbottom="15mm">
    <page_footer>
        <table class="page_footer">
            <tr>
                <td style="width: 100%; text-align: right">
                    page [[page_cu]]/[[page_nb]]
                </td>
            </tr>
        </table>
    </page_footer>
    <table cellpadding="0" cellspacing="0" width="700" border="0">
        <tr>
            <td style="background-image: url(images/bg1.jpg); background-size: contain;">
                <table cellpadding="0" cellspacing="0" width="700" border="0">
                    <tr>
                        <td width="250" align="center">
                            <img src="images/logo-3.png" width="200" />
                        </td>
                        <td align="center" valign="middle" width="450">
                            <h2 style="margin-top: 5px; margin-bottom: 5px; font-size: 32px; text-decoration: underline;">SHIVAM MANUFACTURE</h2>
                            Samrat Industries Area 22/30 Corner, B/h S.T. Workshop, Rajkot. <br />(GUJ). INDIA. Mobile: 75671 98500, 99094 80230
                        </td>    
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <div style="font-size: 24px; font-weight: bold; padding-top: 20px; padding-bottom: 10px;">INVOICE</div>
    <table cellpadding="0" cellspacing="0" width="700" border="0">
        <tr>
            <td align="left" valign="top" width="400" >
                <div class="address" style="padding-left: 20px;">
                    Attention : <?php echo $party_name; ?>
                </div>
            </td>
            <td valign="top" width="300">
                <table width="300" cellpadding="0" cellspacing="0" border="0">
                    <tr>
                        <td width="150" align="right"><b>Invoice Number</b></td>
                        <td width="140" align="right"><?php echo $invoice_number; ?></td>
                    </tr>
                    <tr>
                        <td width="150" align="right"><b>Date</b></td>
                        <td width="140" align="right"><?php echo $invoice_date; ?></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
    <br />
    <br />
    <table cellpadding="0" cellspacing="0" border="0" width='700'>
        <tr class="header">
            <th width="40" class="right">#</th>
            <th width="300" class="right">Description</th>
            <th width="80" class="right" align="right">Price <br/><small>(in <?php echo $currency; ?>)</small></th>
            <th width="80" class="right" align="right">Quantity</th>
            <th width="100" align="right">Amount<br/><small>(in <?php echo $currency; ?>)</small></th>
        </tr>
        <?php
        echo $trhtml;
        ?>
        <tr>
            <td colspan="4" style="height: 25px;">&nbsp;</td>
        </tr>
        <tr class="total">
            <td></td>
            <td align="left"><b>Total</b></td>
            <td></td>
            <td align="right"><b> <?php echo $qty_total; ?></b></td>
            <td align="right"><b> <?php echo $currency; ?> <?php echo number_format($total, 2); ?></b></td>
        </tr>
    </table>

</page>