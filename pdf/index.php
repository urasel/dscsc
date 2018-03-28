<?php
	//require base class
	require_once("html2fpdf/html2fpdf.php");		
	
	//set path and file name of desired PDF	
	$pdf_dir_name = 'report/';	
	$report_id = 'test123';	
	$pdf_file_name = $report_id.'.pdf';		
	$pdf_invoice_name = $pdf_dir_name.$pdf_file_name;
	
	//Create the object and Save PDF file
	$pdf=new HTML2FPDF();
	$pdf->SetFont('courier','',11);
	$pdf->AddPage();
	$content_pdf = getContent(); //set content to print	
	$pdf->WriteHTML($content_pdf);
	$pdf->Output($pdf_invoice_name);
	
	//Download the File
	//comment out if not necessary
	download_report($pdf_invoice_name);

	echo 'Please, Check whether the File created and Saved to <b>'.$pdf_dir_name.'</b> Directory';
	
	
	
/*!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!   Create Output  !!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!*/	
	
	function getContent(){
			$str_pdf = '				
				<table cellpadding="0" cellspacing="0" width="100%" align="center" class="invoice" border="0">
					<tr>	
						<td width="332" valign="top">
							<img src="report/header.jpg" width="141" height="36" border="0" title="labTEX" alt="Invoice for Mr."  />
						</td>				
						<td valign="top" width="333">
							<span><b>Invoice #:</b></span>
							<br  />
							&nbsp;&nbsp;&nbsp;&nbsp;Please quote this number on your payment advice.	
						</td>
					</tr>
					<tr>					
						<td colspan="2"><br /></td>										
					</tr>
					<tr>					
						<td width="332" valign="top">
							<br />
							To:	'.$customerData->name.'<br />
							Customer ID #: '.$customerData->customer_id.'<br />
							Attention: '.$customerData->name.'<br />
							Requester: ???<br />
							Sample: '.$supplierData->sample_desc.'<br />
							Colour: '.$supplierData->sample_color.'
						</td>		
						<td width="333" valign="top">
							<br />
							Invoice Date:  '.$supplierData->sample_color.'<br />
							Report ID:  '.$infoData->report_id.'<br />
							Style #:  '.$buyerData->style_no.'<br />
							Service Type:  '.$infoData->service_name.'<br />
							Report Issue Date:  '.$infoData->report_login_date.'<br />
							Client:  '.$buyerData->client_name.'<br />
							Reference:  &nbsp;<br />
							P.O. #: '.$buyerData->po_no.'	
						</td>
					</tr>					
				</table>
				<br />
				<table cellspacing="1" cellpadding="1" width="100%" border="1" align="center" bgcolor="#646464" class="invoice">
					<tr>
						<td rowspan="2" height="51" bgcolor="#ffffff"><b>QTY</b></td>
						<td rowspan="2" bgcolor="#ffffff"><b>Description/Tests</b></td>
						<td colspan="4" bgcolor="#ffffff"><b>Price, Discount &amp; Surcharge</b></td>					
						<td rowspan="2" bgcolor="#ffffff" align="right"><b>Total (US$)</b></td>
					</tr>
					<tr>
						<td bgcolor="#ffffff"><b>Unit Price</b></td>
						<td bgcolor="#ffffff"><b>Amount</b></td>
						<td bgcolor="#ffffff"><b>Discount %</b></td>
						<td bgcolor="#ffffff"><b>Surcharge %</b></td>
					</tr>
					<tr>
						<td bgcolor="#ffffff">&nbsp;</td>
						<td bgcolor="#ffffff">&nbsp;</td>
						<td align="right" bgcolor="#ffffff">'.$infoData->total.'</td>
						<td align="right" bgcolor="#ffffff">'.$infoData->total.'</td>
						<td align="right" bgcolor="#ffffff">100 %</td>
						<td align="right" bgcolor="#ffffff">'.$infoData->surcharge.' %</td>
						<td align="right" bgcolor="#ffffff">'.number_format($infoData->subtotal,'2','.','').'</td>
					</tr>
					<tr>
						<td align="right" bgcolor="#ffffff" colspan="7"><font color="#ffffff">..</font></td>						
					</tr>
					<tr>
						<td align="right" bgcolor="#ffffff" colspan="6"><b>Total (US$): </b></td>
						<td align="right" bgcolor="#ffffff"><b>'.number_format($infoData->subtotal,'2','.','').'</b></td>
					</tr>
					<tr>
						<td align="right" bgcolor="#ffffff" colspan="6">VAT (Value Added Tax) @ 15%</td>
						<td align="right" bgcolor="#ffffff">'.number_format($infoData->vat,'2','.','').'</td>
					</tr>
					<tr>
						<td align="right" bgcolor="#ffffff" colspan="6">Courier + Communication cost: </td>
						<td align="right" bgcolor="#ffffff">'.number_format(0,'2','.','').'</td>
					</tr>
					<tr>
						<td align="right" bgcolor="#ffffff" colspan="6"><b>Grand Total - US$: </b></td>
						<td align="right" bgcolor="#ffffff"><b>'.number_format($infoData->cost,'2','.','').'</b></td>
					</tr>
					
					<tr>					
						<td colspan="7" bgcolor="#ffffff">In Words: <b>Hundred</b> US Dollar Only.</td>
					</tr>
				</table>
				<br />
				Note: 						
				<ol>
					<li>Exchange Rate: 1 US$ = 68.45 BDT</li>										
					<li>Minimum Invoice value is US$10</li>										
					<li>Payment should be made by Cash/ Cheque/ DD/ TT in the name of <b>Labtex Services</b></li>					
					<li>Bank Information: <b>Prime Bank Ltd.</b>, Account #<b>11015630</b>, Branch: <b>Mohakhali, Dhaka, Bangladesh.</b></li>										
					<li>This is a Computerized Invoice & doesn’t bear authorized Signature</li>										
					<li>labTEX services reserved all rights to make invoice in the name of a Vendor if sample submitted directly by the buyer based on business agreement / prior confirmation from Vendor.</li>										
					<li>Special discounts and prices are not applicable to the standard minimum report charge.</li>										
					<li>Bank, telegraph transfer and any particular country tax charges shall be strictly borne by the invoice recipient.</li>										
				</ol>
				<br />
				<hr  />
				<br />
				<table cellpadding="0" cellspacing="0" width="100%" align="center" class="invoice" border="0">
					<tr>	
						<td width="332" valign="top">
							<img src="report/header.jpg" width="141" height="36" border="0" title="labTEX" alt="Invoice for Mr."  />
						</td>				
						<td valign="top" width="333">
							<span><b>Invoice #:</b></span>
							<br  />
							&nbsp;&nbsp;&nbsp;&nbsp;Please quote this number on your payment advice.	
						</td>
					</tr>
					<tr>					
						<td colspan="2"><br /></td>										
					</tr>
					<tr>					
						<td width="332" valign="top">
							<br />
							To:	'.$customerData->name.'<br />
							Customer ID #: '.$customerData->customer_id.'<br />
							Attention: '.$customerData->name.'<br />
							Requester: ???<br />
							Sample: '.$supplierData->sample_desc.'<br />
							Colour: '.$supplierData->sample_color.'
						</td>		
						<td width="333" valign="top">
							<br />
							Invoice Date:  '.$supplierData->sample_color.'<br />
							Report ID:  '.$infoData->report_id.'<br />
							Style #:  '.$buyerData->style_no.'<br />
							Service Type:  '.$infoData->service_name.'<br />
							Report Issue Date:  '.$infoData->report_login_date.'<br />
							Client:  '.$buyerData->client_name.'<br />
							Reference:  &nbsp;<br />
							P.O. #: '.$buyerData->po_no.'	
						</td>
					</tr>					
				</table>
				<br />
				<table cellspacing="1" cellpadding="1" width="100%" border="1" align="center" bgcolor="#646464" class="invoice">
					<tr>
						<td rowspan="2" height="51" bgcolor="#ffffff"><b>QTY</b></td>
						<td rowspan="2" bgcolor="#ffffff"><b>Description/Tests</b></td>
						<td colspan="4" bgcolor="#ffffff"><b>Price, Discount &amp; Surcharge</b></td>					
						<td rowspan="2" bgcolor="#ffffff" align="right"><b>Total (US$)</b></td>
					</tr>
					<tr>
						<td bgcolor="#ffffff"><b>Unit Price</b></td>
						<td bgcolor="#ffffff"><b>Amount</b></td>
						<td bgcolor="#ffffff"><b>Discount %</b></td>
						<td bgcolor="#ffffff"><b>Surcharge %</b></td>
					</tr>
					<tr>
						<td bgcolor="#ffffff">&nbsp;</td>
						<td bgcolor="#ffffff">&nbsp;</td>
						<td align="right" bgcolor="#ffffff">'.$infoData->total.'</td>
						<td align="right" bgcolor="#ffffff">'.$infoData->total.'</td>
						<td align="right" bgcolor="#ffffff">100 %</td>
						<td align="right" bgcolor="#ffffff">'.$infoData->surcharge.' %</td>
						<td align="right" bgcolor="#ffffff">'.number_format($infoData->subtotal,'2','.','').'</td>
					</tr>
					<tr>
						<td align="right" bgcolor="#ffffff" colspan="7"><font color="#ffffff">..</font></td>						
					</tr>
					<tr>
						<td align="right" bgcolor="#ffffff" colspan="6"><b>Total (US$): </b></td>
						<td align="right" bgcolor="#ffffff"><b>'.number_format($infoData->subtotal,'2','.','').'</b></td>
					</tr>
					<tr>
						<td align="right" bgcolor="#ffffff" colspan="6">VAT (Value Added Tax) @ 15%</td>
						<td align="right" bgcolor="#ffffff">'.number_format($infoData->vat,'2','.','').'</td>
					</tr>
					<tr>
						<td align="right" bgcolor="#ffffff" colspan="6">Courier + Communication cost: </td>
						<td align="right" bgcolor="#ffffff">'.number_format(0,'2','.','').'</td>
					</tr>
					<tr>
						<td align="right" bgcolor="#ffffff" colspan="6"><b>Grand Total - US$: </b></td>
						<td align="right" bgcolor="#ffffff"><b>'.number_format($infoData->cost,'2','.','').'</b></td>
					</tr>
					
					<tr>					
						<td colspan="7" bgcolor="#ffffff">In Words: <b>Hundred</b> US Dollar Only.</td>
					</tr>
				</table>
				<br />
				Note: 						
				<ol>
					<li>Exchange Rate: 1 US$ = 68.45 BDT</li>										
					<li>Minimum Invoice value is US$10</li>										
					<li>Payment should be made by Cash/ Cheque/ DD/ TT in the name of <b>Labtex Services</b></li>					
					<li>Bank Information: <b>Prime Bank Ltd.</b>, Account #<b>11015630</b>, Branch: <b>Mohakhali, Dhaka, Bangladesh.</b></li>										
					<li>This is a Computerized Invoice & doesn’t bear authorized Signature</li>										
					<li>labTEX services reserved all rights to make invoice in the name of a Vendor if sample submitted directly by the buyer based on business agreement / prior confirmation from Vendor.</li>										
					<li>Special discounts and prices are not applicable to the standard minimum report charge.</li>										
					<li>Bank, telegraph transfer and any particular country tax charges shall be strictly borne by the invoice recipient.</li>										
				</ol>
							
			';
			
		//$response = array();
		//$response['html'] = $str;
		//$response['pdf'] = $str_pdf;
		$response = $str_pdf;	
		return $response;	
	}	
	
	function download_report($pdf_invoice_name){
		// We'll be outputting a PDF
		header('Content-type: application/pdf');
		
		// It will be called downloaded.pdf
		header('Content-Disposition: attachment; filename="download.pdf"');
		
		// The PDF source is in original.pdf
		readfile($pdf_invoice_name);
	}	
?>