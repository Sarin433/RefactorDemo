<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Validation Language Lines (ภาษาไทย)
    |--------------------------------------------------------------------------
    */

    'accepted' => ':attribute ต้องได้รับการยอมรับ',
    'accepted_if' => ':attribute ต้องได้รับการยอมรับเมื่อ :other เป็น :value',
    'active_url' => ':attribute ไม่ใช่ URL ที่ถูกต้อง',
    'after' => ':attribute ต้องเป็นวันที่หลังจาก :date',
    'after_or_equal' => ':attribute ต้องเป็นวันที่หลังจากหรือเท่ากับ :date',
    'alpha' => ':attribute ต้องประกอบด้วยตัวอักษรเท่านั้น',
    'alpha_dash' => ':attribute ต้องประกอบด้วยตัวอักษร ตัวเลข ขีดกลาง และขีดล่าง เท่านั้น',
    'alpha_num' => ':attribute ต้องประกอบด้วยตัวอักษรและตัวเลขเท่านั้น',
    'any_of' => ':attribute ไม่ถูกต้อง',
    'array' => ':attribute ต้องเป็นอาร์เรย์',
    'ascii' => ':attribute ต้องประกอบด้วยตัวอักษรและสัญลักษณ์แบบ single-byte เท่านั้น',
    'before' => ':attribute ต้องเป็นวันที่ก่อน :date',
    'before_or_equal' => ':attribute ต้องเป็นวันที่ก่อนหรือเท่ากับ :date',
    'between' => [
        'array' => ':attribute ต้องมีระหว่าง :min ถึง :max รายการ',
        'file' => ':attribute ต้องมีขนาดระหว่าง :min ถึง :max กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าระหว่าง :min ถึง :max',
        'string' => ':attribute ต้องมีความยาวระหว่าง :min ถึง :max ตัวอักษร',
    ],
    'boolean' => ':attribute ต้องเป็นจริงหรือเท็จ',
    'can' => ':attribute มีค่าที่ไม่ได้รับอนุญาต',
    'confirmed' => 'การยืนยัน :attribute ไม่ตรงกัน',
    'contains' => ':attribute ขาดค่าที่จำเป็น',
    'current_password' => 'รหัสผ่านไม่ถูกต้อง',
    'date' => ':attribute ต้องเป็นวันที่ที่ถูกต้อง',
    'date_equals' => ':attribute ต้องเป็นวันที่เท่ากับ :date',
    'date_format' => ':attribute ไม่ตรงกับรูปแบบ :format',
    'decimal' => ':attribute ต้องมี :decimal ตำแหน่งทศนิยม',
    'declined' => ':attribute ต้องถูกปฏิเสธ',
    'declined_if' => ':attribute ต้องถูกปฏิเสธเมื่อ :other เป็น :value',
    'different' => ':attribute และ :other ต้องแตกต่างกัน',
    'digits' => ':attribute ต้องเป็นตัวเลข :digits หลัก',
    'digits_between' => ':attribute ต้องมีระหว่าง :min ถึง :max หลัก',
    'dimensions' => ':attribute มีขนาดรูปภาพที่ไม่ถูกต้อง',
    'distinct' => ':attribute มีค่าที่ซ้ำกัน',
    'doesnt_contain' => ':attribute ต้องไม่มีค่าต่อไปนี้: :values',
    'doesnt_end_with' => ':attribute ต้องไม่ลงท้ายด้วย: :values',
    'doesnt_start_with' => ':attribute ต้องไม่ขึ้นต้นด้วย: :values',
    'email' => ':attribute ต้องเป็นอีเมลที่ถูกต้อง',
    'ends_with' => ':attribute ต้องลงท้ายด้วย: :values',
    'enum' => ':attribute ที่เลือกไม่ถูกต้อง',
    'exists' => ':attribute ที่เลือกไม่ถูกต้อง',
    'extensions' => ':attribute ต้องมีนามสกุลไฟล์เป็น: :values',
    'file' => ':attribute ต้องเป็นไฟล์',
    'filled' => ':attribute ต้องมีค่า',
    'gt' => [
        'array' => ':attribute ต้องมีมากกว่า :value รายการ',
        'file' => ':attribute ต้องมีขนาดมากกว่า :value กิโลไบต์',
        'numeric' => ':attribute ต้องมากกว่า :value',
        'string' => ':attribute ต้องมีมากกว่า :value ตัวอักษร',
    ],
    'gte' => [
        'array' => ':attribute ต้องมีอย่างน้อย :value รายการ',
        'file' => ':attribute ต้องมีขนาดอย่างน้อย :value กิโลไบต์',
        'numeric' => ':attribute ต้องมากกว่าหรือเท่ากับ :value',
        'string' => ':attribute ต้องมีอย่างน้อย :value ตัวอักษร',
    ],
    'hex_color' => ':attribute ต้องเป็นรหัสสีฐานสิบหกที่ถูกต้อง',
    'image' => ':attribute ต้องเป็นรูปภาพ',
    'in' => ':attribute ที่เลือกไม่ถูกต้อง',
    'in_array' => ':attribute ไม่มีอยู่ใน :other',
    'integer' => ':attribute ต้องเป็นจำนวนเต็ม',
    'ip' => ':attribute ต้องเป็น IP address ที่ถูกต้อง',
    'ipv4' => ':attribute ต้องเป็น IPv4 address ที่ถูกต้อง',
    'ipv6' => ':attribute ต้องเป็น IPv6 address ที่ถูกต้อง',
    'json' => ':attribute ต้องเป็น JSON string ที่ถูกต้อง',
    'list' => ':attribute ต้องเป็นรายการ',
    'lowercase' => ':attribute ต้องเป็นตัวพิมพ์เล็ก',
    'lt' => [
        'array' => ':attribute ต้องมีน้อยกว่า :value รายการ',
        'file' => ':attribute ต้องมีขนาดน้อยกว่า :value กิโลไบต์',
        'numeric' => ':attribute ต้องน้อยกว่า :value',
        'string' => ':attribute ต้องมีน้อยกว่า :value ตัวอักษร',
    ],
    'lte' => [
        'array' => ':attribute ต้องไม่มีมากกว่า :value รายการ',
        'file' => ':attribute ต้องมีขนาดไม่เกิน :value กิโลไบต์',
        'numeric' => ':attribute ต้องน้อยกว่าหรือเท่ากับ :value',
        'string' => ':attribute ต้องมีไม่เกิน :value ตัวอักษร',
    ],
    'mac_address' => ':attribute ต้องเป็น MAC address ที่ถูกต้อง',
    'max' => [
        'array' => ':attribute ต้องไม่มีเกิน :max รายการ',
        'file' => ':attribute ต้องไม่เกิน :max กิโลไบต์',
        'numeric' => ':attribute ต้องไม่เกิน :max',
        'string' => ':attribute ต้องไม่เกิน :max ตัวอักษร',
    ],
    'max_digits' => ':attribute ต้องไม่เกิน :max หลัก',
    'mimes' => ':attribute ต้องเป็นไฟล์ชนิด: :values',
    'mimetypes' => ':attribute ต้องเป็นไฟล์ชนิด: :values',
    'min' => [
        'array' => ':attribute ต้องมีอย่างน้อย :min รายการ',
        'file' => ':attribute ต้องมีขนาดอย่างน้อย :min กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่าอย่างน้อย :min',
        'string' => ':attribute ต้องมีอย่างน้อย :min ตัวอักษร',
    ],
    'min_digits' => ':attribute ต้องมีอย่างน้อย :min หลัก',
    'missing' => ':attribute ต้องไม่มี',
    'missing_if' => ':attribute ต้องไม่มีเมื่อ :other เป็น :value',
    'missing_unless' => ':attribute ต้องไม่มีเว้นแต่ :other เป็น :value',
    'missing_with' => ':attribute ต้องไม่มีเมื่อมี :values',
    'missing_with_all' => ':attribute ต้องไม่มีเมื่อมี :values ทั้งหมด',
    'multiple_of' => ':attribute ต้องเป็นจำนวนทวีคูณของ :value',
    'not_in' => ':attribute ที่เลือกไม่ถูกต้อง',
    'not_regex' => ':attribute มีรูปแบบที่ไม่ถูกต้อง',
    'numeric' => ':attribute ต้องเป็นตัวเลข',
    'password' => [
        'letters' => ':attribute ต้องมีตัวอักษรอย่างน้อย 1 ตัว',
        'mixed' => ':attribute ต้องมีตัวพิมพ์ใหญ่และตัวพิมพ์เล็กอย่างน้อยอย่างละ 1 ตัว',
        'numbers' => ':attribute ต้องมีตัวเลขอย่างน้อย 1 ตัว',
        'symbols' => ':attribute ต้องมีสัญลักษณ์อย่างน้อย 1 ตัว',
        'uncompromised' => ':attribute ที่ให้มาพบในข้อมูลที่รั่วไหล กรุณาเลือก :attribute อื่น',
    ],
    'present' => ':attribute ต้องมีอยู่',
    'present_if' => ':attribute ต้องมีอยู่เมื่อ :other เป็น :value',
    'present_unless' => ':attribute ต้องมีอยู่เว้นแต่ :other เป็น :value',
    'present_with' => ':attribute ต้องมีอยู่เมื่อมี :values',
    'present_with_all' => ':attribute ต้องมีอยู่เมื่อมี :values ทั้งหมด',
    'prohibited' => ':attribute ถูกห้าม',
    'prohibited_if' => ':attribute ถูกห้ามเมื่อ :other เป็น :value',
    'prohibited_unless' => ':attribute ถูกห้ามเว้นแต่ :other อยู่ใน :values',
    'prohibits' => ':attribute ห้ามให้ :other มีอยู่',
    'regex' => ':attribute มีรูปแบบที่ไม่ถูกต้อง',
    'required' => 'กรุณากรอก :attribute',
    'required_array_keys' => ':attribute ต้องมีรายการสำหรับ: :values',
    'required_if' => ':attribute จำเป็นเมื่อ :other เป็น :value',
    'required_if_accepted' => ':attribute จำเป็นเมื่อ :other ได้รับการยอมรับ',
    'required_if_declined' => ':attribute จำเป็นเมื่อ :other ถูกปฏิเสธ',
    'required_unless' => ':attribute จำเป็นเว้นแต่ :other อยู่ใน :values',
    'required_with' => ':attribute จำเป็นเมื่อมี :values',
    'required_with_all' => ':attribute จำเป็นเมื่อมี :values ทั้งหมด',
    'required_without' => ':attribute จำเป็นเมื่อไม่มี :values',
    'required_without_all' => ':attribute จำเป็นเมื่อไม่มี :values ทั้งหมด',
    'same' => ':attribute และ :other ต้องตรงกัน',
    'size' => [
        'array' => ':attribute ต้องมี :size รายการ',
        'file' => ':attribute ต้องมีขนาด :size กิโลไบต์',
        'numeric' => ':attribute ต้องมีค่า :size',
        'string' => ':attribute ต้องมี :size ตัวอักษร',
    ],
    'starts_with' => ':attribute ต้องขึ้นต้นด้วย: :values',
    'string' => ':attribute ต้องเป็นสตริง',
    'timezone' => ':attribute ต้องเป็นเขตเวลาที่ถูกต้อง',
    'unique' => ':attribute ถูกใช้งานแล้ว',
    'uploaded' => ':attribute อัปโหลดล้มเหลว',
    'uppercase' => ':attribute ต้องเป็นตัวพิมพ์ใหญ่',
    'url' => ':attribute ต้องเป็น URL ที่ถูกต้อง',
    'ulid' => ':attribute ต้องเป็น ULID ที่ถูกต้อง',
    'uuid' => ':attribute ต้องเป็น UUID ที่ถูกต้อง',

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Language Lines
    |--------------------------------------------------------------------------
    */

    'custom' => [
        'attribute-name' => [
            'rule-name' => 'custom-message',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Custom Validation Attributes
    |--------------------------------------------------------------------------
    */

    'attributes' => [
        'email'                 => 'อีเมล',
        'first_name'            => 'ชื่อจริง',
        'last_name'             => 'นามสกุล',
        'phone'                 => 'เบอร์โทรศัพท์',
        'password'              => 'รหัสผ่าน',
        'password_confirmation' => 'ยืนยันรหัสผ่าน',
        'shipping_address'      => 'ที่อยู่จัดส่ง',
        'quantity'              => 'จำนวน',
        'order_ids'             => 'รายการคำสั่งซื้อ',
    ],

];
