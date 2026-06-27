$excel = New-Object -ComObject Excel.Application
$excel.Visible = $false
$workbook = $excel.Workbooks.Open("G:\doc\2567\CCTV\cctv.xls")
$worksheet = $workbook.Sheets.Item(1)

# Read first 5 rows, 10 columns
for ($row=1; $row -le 5; $row++) {
    $rowData = @()
    for ($col=1; $col -le 10; $col++) {
        $cell = $worksheet.Cells.Item($row, $col)
        $rowData += '"' + $cell.Text + '"'
    }
    Write-Host ($rowData -join ",")
}

$workbook.Close($false)
$excel.Quit()
[System.Runtime.Interopservices.Marshal]::ReleaseComObject($excel) | Out-Null
