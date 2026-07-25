import openpyxl
import json

try:
    wb = openpyxl.load_workbook('/Users/haideraltemimy/Documents/GitHub/KarbalaDentalAssociation/معاملات نقابة أسنان كربلاء المقدسة.xlsx')
    ws = wb.active
    data = []
    for i, row in enumerate(ws.iter_rows(values_only=True)):
        if i == 0:  # Skip header
            continue
        if row and len(row) > 1 and row[0]:
            data.append({
                'name': str(row[0]).strip(),
                'transaction_type': str(row[1]).strip(),
            })
    print(json.dumps(data))
except Exception as e:
    print(json.dumps([]))