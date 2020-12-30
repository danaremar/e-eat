<h1>Credit Note</h1>
<p>
<table class="pdf_invoice_heading_table">
<tbody>
	<tr><th>Credit Note Nr.</th><td>[wcj_credit_note_number]</td></tr>
	<tr><th>Credit Note Date</th><td>[wcj_credit_note_date]</td></tr>
	<tr><th>Invoice Nr.</th><td>[wcj_invoice_number]</td></tr>
	<tr><th>Order Nr.</th><td>[wcj_order_number]</td></tr>
</tbody>
</table>
</p>
<p>
<table class="pdf_invoice_seller_buyer_table">
<tbody>
	<tr><th>Seller</th><th>Buyer</th></tr>
	<tr><td>COMPANY NAME<br>COMPANY ADDRESS 1<br>COMPANY ADDRESS 2<br></td><td>[wcj_order_billing_address]</td></tr>
</tbody>
</table>
</p>
<p>
[wcj_order_items_table table_class="pdf_invoice_items_table" price_prefix="-"
	columns="item_number|item_name|item_quantity|line_total_tax_excl"
	columns_titles="|Product|Qty|Total"
	columns_styles="width:5%;|width:75%;|width:5%;|width:15%;text-align:right;"]
<table class="pdf_invoice_totals_table">
<tbody>
	<tr><th>Total (excl. TAX)</th><td>-[wcj_order_total_excl_tax]</td></tr>
	<tr><th>Taxes</th><td>-[wcj_order_total_tax hide_if_zero="no"]</td></tr>
	<tr><th>Order Total</th><td>-[wcj_order_total]</td></tr>
</tbody>
</table>
</p>
<p>Payment method: [wcj_order_payment_method]</p>