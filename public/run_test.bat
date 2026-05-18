@echo off
cd /d "D:\Semester 6\PA 3\Project\PA3"
php artisan ocr:test-easyocr --image=model\dataset\Test\dataset_51.png
pause
