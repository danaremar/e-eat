<h1>Packing Slip</h1>
<p>
<table class="pdf_invoice_heading_table">
<tbody>
	<tr><th>Shipping method</th><td>[wcj_order_shipping_method]</td></tr>
	<tr><th>Proforma Invoice Nr.</th><td>[wcj_proforma_invoice_number]</td></tr>
	<tr><th>Invoice Nr.</th><td>[wcj_invoice_number]</td></tr>
	<tr><th>Order Nr.</th><td>[wcj_order_number]</td></tr>
	<tr><th>Order Date</th><td>[wcj_order_date]</td></tr>
</tbody>
</table>
</p>
<h2>Shipping address</h2>
<p>
<table>
<tbody>
	<tr><td>[wcj_order_shipping_address]</td></tr>
</tbody>
</table>
</p>
<h2>Items</h2>
<p>
[wcj_order_items_table table_class="pdf_invoice_items_table"
	columns="item_number|item_name|item_quantity"
	columns_titles="|Item|Qty"
	columns_styles="width:5%;|width:80%;|width:15%;text-align:right;"]
</p>