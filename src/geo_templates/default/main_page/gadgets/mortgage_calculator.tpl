
{* The HTML that is displayed within the real estate category id. *}

<!-- START MORTAGE CALCULATOR -->

<div>
<form method="post" action="http://www.mortgagecalculator.org" target="_blank">
<input type="hidden" name="param[action]" value="calculate">
<input type="hidden" name="mortgage-calculator-plus" value="2b0b2e2f885a8e63c88ab2efb3775a11">
<table style="text-align:center"><tr><td id="params" valign="top">
<table style="text-align:center">
<tr><th colspan="2" align="center"><img src="{external file='images/mortgage_calc.png'}" alt="Mortgage Calculator" height="26" width="210" border="0">
</th></tr>
<tr><td align="right" width="85">Home Value:</td><td><input type="text" name="param[homevalue]" value="300,000" size="10"> $</td></tr> 
<tr><td align="right">Loan amount:</td><td><input type="text" name="param[principal]" value="250,000" size="10"> $</td></tr>
<tr><td align="right">Interest rate:</td><td><input type="text" name="param[interest_rate]" value="6.5" size="4"> %</td></tr>
<tr><td align="right">Loan term:</td><td><input type="text" name="param[term]" value="30" size="4"> years</td></tr>
<tr><td align="right">Start date:</td><td><select name="param[start_month]">
<option label="Jan" value="1">Jan</option>
<option label="Feb" value="2">Feb</option>
<option label="Mar" value="3">Mar</option>
<option label="Apr" value="4">Apr</option>
<option label="May" value="5">May</option>
<option label="Jun" value="6">Jun</option>
<option label="Jul" value="7">Jul</option>
<option label="Aug" value="8">Aug</option>
<option label="Sep" value="9">Sep</option>
<option label="Oct" value="10">Oct</option>
<option label="Nov" value="11">Nov</option>
<option label="Dec" value="12">Dec</option>
</select>
<select name="param[start_year]">
<option label="2018" value="2018">2018</option>
<option label="2019" value="2019">2019</option>
<option label="2020" value="2020">2020</option>
<option label="2021" value="2021">2021</option>
<option label="2022" value="2022">2022</option>
<option label="2023" value="2023">2023</option>
<option label="2024" value="2024">2024</option>
<option label="2025" value="2025">2025</option>
<option label="2026" value="2026">2026</option>
<option label="2027" value="2027">2027</option>
<option label="2028" value="2028">2028</option>
<option label="2029" value="2029">2029</option>
<option label="2030" value="2030">2030</option>
</select>
</td></tr>
<tr><td align="right">Property tax:</td><td><input type="text" name="param[property_tax]" value="1.25" size="4"> %</td></tr>
<tr><td align="right">PMI:</td><td><input type="text" name="param[pmi]" value="0.5" size="4"> %</td></tr>
<tr><td colspan="2" align="center" id="calculate_btn"><input type="submit" value="Calculate"></td></tr>
</table>
</td></tr>
</table></form>
</div>

<!-- END MORTAGE CALCULATOR -->