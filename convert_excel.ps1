$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$excel.DisplayAlerts = $false
$workbook = $excel.Workbooks.Open("G:\doc\2567\CCTV\cctv.xls")
$workbook.SaveAs("c:\wamp64\www\rescu90\cctv_data.csv", 6) # 6 = xlCSV
$workbook.Close($false)
$excel.Quit()
[System.Runtime.Interopservices.Marshal]::ReleaseComObject($excel) | Out-Null
