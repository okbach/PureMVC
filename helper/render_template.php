<?php



function renderTemplate($templatePath, $data) {
    ob_start(); // بدء الـ output buffering
    extract($data); // تحويل المصفوفة إلى متغيرات
    include $templatePath; // تحميل القالب
    return ob_get_clean(); // إرجاع المحتوى كـ string
}

?>