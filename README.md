# MindBridger 오픈 소스 AI 도서관 시스템에 오신 것을 환영합니다 (Laravel 메인 저장소)
# Welcome to MindBridger Open Source AI Library System (Main Repo of Laravel): See below for English Guidance

## 환경
  - Apache 웹 서버 2.4.* +
  - MariaDB(MySQL) 10.4.* +
  - PHP 8.0.2 +
  - Laravel 9.52.18

1. 메인 Laravel 저장소 (백엔드 + 프론트엔드)
   - https://github.com/wisesam/mindbridger-main.git
   - 디렉토리 권한 조정이 필요할 수 있음 (Linux와 MacOS):
     - 예: chown -R apache mindbridger-main, chgrp -R apache mindbridger-main
 -  composer install
2. 웹/관리자 프로그램 설치 https://github.com/wisesam/mindbridger.git
3. cp .env.example .env  (수정 필요)
4. cp config/app(default).php config/app.php (수정 필요)
5. cp config/database(default).php config/database.php (수정 필요)
6.
7. php artisan key:generate (.env 업데이트)
8. DB 마이그레이션 (도서관 관련 테이블 설치)
   - php artisan migrate
   - php artisan db:seed --class=CodeSeeder
   - (마이그레이션 시 "No such file or directory" 오류가 발생하면, .env의 DB_SOCKET을 업데이트)

9. 심볼릭 링크 생성 (책 표지 이미지용)
   - mindbridger 루트 디렉토리로 이동 (예: c:\xampp\htdocs\mindbridger, /var/www/html/mindbridger)
   - (Windows CMD 예시)  mklink /D storage "c:\xampp\mindbridger-main\storage\app\public"
   - (Windows PowerShell 예시) New-Item -ItemType SymbolicLink -Path "storage" -Target "C:\xampp\mindbridger-main\storage\app\public"
   - (Linux, MacOS 예시) ln -s /var/www/mindbridger-main/storage/app/public storage
10. http://localhost/mindbridger/vwmldbm 에서 Update 버튼 클릭
       
## 데모
<a href="https://wise4edu.net/mindbridger" target="_blank">MindBridger 데모</a>

## 서버 사이드 렌더링과 최소 라이브러리
- 오래된 스마트폰에서도 구동할 수 있도록 가볍게 만들기 위해 webpack, vite는 사용하지 않았습니다.

## Laravel 소개
Laravel은 표현력이 풍부하고 우아한 문법을 가진 웹 애플리케이션 프레임워크입니다. 우리는 개발이 진정으로 만족스러우려면 즐겁고 창의적인 경험이어야 한다고 믿습니다. Laravel은 많은 웹 프로젝트에서 공통적으로 사용되는 작업들을 단순화하여 개발의 고통을 덜어줍니다. 예를 들어:


## Environment
  - Apache Web Server 2.4.* +
  - MariaDB(MySQL) 10.4.* +
  - PHP 8.0.2 +
  - Laravel 9.52.18

##  First thing to do is installing web/admin program:
### https://github.com/wisesam/mindbridger.git

## Now you can install MindBridger Main Laravel program
1. Main Larabel repo (backend + frontend)
   - https://github.com/wisesam/mindbridger-main.git
   - Adujust directory permission if needed (Linux and MacOS):
     - eg, chown -R apache mindbridger-main, chgrp -R apache mindbridger-main
3. cp .env.example .env  (update needed)
4. cp config/app(default).php config/app.php (update needed)
5. cp config/database (default).php config/database .php (update needed)
6. composer install
7. php artisan key:generate (update .env)
8. DB Migration (Library related table installation)
   - php artisan migrate
   - php artisan db:seed --class=CodeSeeder
   - (If migration gives error like " No such file or directory", then update DB_SOCKET in .env)

9. Create a symbolic link (for cover images)
   - Go to root of mindbridger (eg, c:\xampp\htdocs\mindbridger, /var/www/html/mindbridger)
   - (Windows CMD example)  mklink /D storage "c:\xampp\mindbridger-main\storage\app\public"
   - (Window PowerShell example) New-Item -ItemType SymbolicLink -Path "storage" -Target "C:\xampp\mindbridger-main\storage\app\public"
   - (Linux, MacOS example) ln -s /var/www/mindbridger-main/storage/app/public storage
10. Click Update button from http://localhost/mindbridger/vwmldbm
       
## Demo
<a href="https://wise4edu.net/mindbridger" target="_blank">MindBridger Demo</a>

## Server Side Rendering and Minimun Libraries
- to make it lightweighted to even run fron old smartphones, no webpack, vite were used.

## About Laravel
Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

