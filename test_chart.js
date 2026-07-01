const records = [
    {
      "_id": 1,
      "จังหวัด": "ภ.จว.พัทลุง",
      "สถานีตำรวจ": "สภ.บางแก้ว ",
      "วันที่ยึดทรัพย์": 14,
      "เดือนที่ยึดทรัพย์": "ตุลาคม",
      "ปีที่ยึดทรัพย์": 2568,
      "ทรัพย์ที่เกี่ยวเนื่อง": "มูลค่าทรัพย์ที่เกี่ยวเนื่อง",
      "ประเภททรัพย์": "เงินสด",
      "จำนวน": 110,
      "ที่มา": "ตำรวจภูธรจังหวัดพัทลุง"
    }
  ];

  const keys = Object.keys(records[0]);
  let numericKey = null;
  let yearKey = null;
  let categoryKey2 = null;

  for (let key of keys) {
      if (key !== '_id' && !key.includes('ปี') && !isNaN(records[0][key])) {
          numericKey = key; 
      }
      if (key.includes('ปี')) {
          yearKey = key; 
      }
      if (isNaN(records[0][key]) && key !== 'ที่มา' && key !== 'หน่วย' && key.length < 20 && !key.includes('ปี')) {
          if (!categoryKey2 && key !== 'จังหวัด') categoryKey2 = key; 
      }
  }

  console.log("numericKey:", numericKey);
  console.log("yearKey:", yearKey);
  console.log("categoryKey2:", categoryKey2);
