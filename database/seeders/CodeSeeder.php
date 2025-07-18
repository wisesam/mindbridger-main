<?php
namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CodeSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        DB::table('code_c_utype')->delete();

        DB::table('code_c_utype')->insert([
            ['inst' => 1, 'code' => 10, 'c_lang' => 10, 'name' => 'Gold Member', 'max_book' => '5', 'max_book_rent_days' => '28', 'max_extend_times'=>'2', 'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 10, 'c_lang' => 30, 'name' => '곧르 멤버', 'max_book' => '5', 'max_book_rent_days' => '28', 'max_extend_times'=>'2', 'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 10, 'name' => 'Member', 'max_book' => '3', 'max_book_rent_days' => '14', 'max_extend_times'=>'2', 'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 30, 'name' => '멤버', 'max_book' => '3', 'max_book_rent_days' => '14', 'max_extend_times'=>'2', 'use_yn' => 'Y'],
        ]);


        DB::table('code_c_rtype')->delete();

        DB::table('code_c_rtype')->insert([
            ['inst' => 1, 'code' => 10, 'c_lang' => 10, 'name' => 'Book',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 10, 'c_lang' => 30, 'name' => '책',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 10, 'name' => 'Article',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 30, 'name' => '기사',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 30, 'c_lang' => 10, 'name' => 'Journal',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 30, 'c_lang' => 30, 'name' => '저널',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 40, 'c_lang' => 10, 'name' => 'Magazine',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 40, 'c_lang' => 30, 'name' => '잡지',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 50, 'c_lang' => 10, 'name' => 'Thesis',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 50, 'c_lang' => 30, 'name' => '논문',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 60, 'c_lang' => 10, 'name' => 'Lecture',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 60, 'c_lang' => 30, 'name' => '강의',   'use_yn' => 'Y'],
        ]);

        DB::table('code_c_category')->delete();
        DB::table('code_c_genre')->delete();

        DB::table('code_c_genre')->insert([
            ['inst' => 1, 'code' => 10, 'c_lang' => 10, 'name' => 'Fiction',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 10, 'c_lang' => 30, 'name' => '소설',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 10, 'name' => 'Essay',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 30, 'name' => '수필',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 30, 'c_lang' => 10, 'name' => 'Poetry',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 30, 'c_lang' => 30, 'name' => '시',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 40, 'c_lang' => 10, 'name' => 'Biography',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 40, 'c_lang' => 30, 'name' => '전기',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 50, 'c_lang' => 10, 'name' => 'History',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 50, 'c_lang' => 30, 'name' => '역사',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 60, 'c_lang' => 10, 'name' => 'Science',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 60, 'c_lang' => 30, 'name' => '과학',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 70, 'c_lang' => 10, 'name' => 'Technology',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 70, 'c_lang' => 30, 'name' => '기술',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 80, 'c_lang' => 10, 'name' => 'Art',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 80, 'c_lang' => 30, 'name' => '예술',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 90, 'c_lang' => 10, 'name' => 'Philosophy',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 90, 'c_lang' => 30, 'name' => '철학',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 100, 'c_lang' => 10, 'name' => 'Socioloy',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 100, 'c_lang' => 30, 'name' => '사회',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 110, 'c_lang' => 10, 'name' => 'Hobby',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 110, 'c_lang' => 30, 'name' => '취미',   'use_yn' => 'Y'],
        ]);

        DB::table('code_c_category')->insert([
            // Fiction (c_genre: 10)
            ['inst' => 1, 'c_genre' => 10, 'code' => 1, 'c_lang' => 10, 'name' => 'Fantasy',    'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 10, 'code' => 1, 'c_lang' => 30, 'name' => '판타지',     'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 10, 'code' => 2, 'c_lang' => 10, 'name' => 'Mystery',    'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 10, 'code' => 2, 'c_lang' => 30, 'name' => '추리',       'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 10, 'code' => 3, 'c_lang' => 10, 'name' => 'Romance',    'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 10, 'code' => 3, 'c_lang' => 30, 'name' => '로맨스',     'use_yn' => 'Y'],
        
            // Essay (c_genre: 20)
            ['inst' => 1, 'c_genre' => 20, 'code' => 1, 'c_lang' => 10, 'name' => 'Memoir',     'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 20, 'code' => 1, 'c_lang' => 30, 'name' => '회고록',     'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 20, 'code' => 2, 'c_lang' => 10, 'name' => 'Opinion',    'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 20, 'code' => 2, 'c_lang' => 30, 'name' => '시론',       'use_yn' => 'Y'],
        
            // Poetry (c_genre: 30)
            ['inst' => 1, 'c_genre' => 30, 'code' => 1, 'c_lang' => 10, 'name' => 'Modern Poetry', 'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 30, 'code' => 1, 'c_lang' => 30, 'name' => '현대시',     'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 30, 'code' => 2, 'c_lang' => 10, 'name' => 'Classic Poetry', 'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 30, 'code' => 2, 'c_lang' => 30, 'name' => '고전시',     'use_yn' => 'Y'],
        
            // Biography (c_genre: 40)
            ['inst' => 1, 'c_genre' => 40, 'code' => 1, 'c_lang' => 10, 'name' => 'Autobiography', 'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 40, 'code' => 1, 'c_lang' => 30, 'name' => '자서전',     'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 40, 'code' => 2, 'c_lang' => 10, 'name' => 'Biography of Others', 'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 40, 'code' => 2, 'c_lang' => 30, 'name' => '타인 전기',   'use_yn' => 'Y'],
        
            // History (c_genre: 50)
            ['inst' => 1, 'c_genre' => 50, 'code' => 1, 'c_lang' => 10, 'name' => 'World History', 'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 50, 'code' => 1, 'c_lang' => 30, 'name' => '세계사',     'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 50, 'code' => 2, 'c_lang' => 10, 'name' => 'National History', 'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 50, 'code' => 2, 'c_lang' => 30, 'name' => '국사',       'use_yn' => 'Y'],
        
            // Science (c_genre: 60)
            ['inst' => 1, 'c_genre' => 60, 'code' => 1, 'c_lang' => 10, 'name' => 'Physics',    'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 60, 'code' => 1, 'c_lang' => 30, 'name' => '물리학',     'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 60, 'code' => 2, 'c_lang' => 10, 'name' => 'Biology',    'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 60, 'code' => 2, 'c_lang' => 30, 'name' => '생물학',     'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 60, 'code' => 3, 'c_lang' => 10, 'name' => 'Astronomy',  'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 60, 'code' => 3, 'c_lang' => 30, 'name' => '천문학',     'use_yn' => 'Y'],

             // Technology (c_genre: 70)
            ['inst' => 1, 'c_genre' => 70, 'code' => 1, 'c_lang' => 10, 'name' => 'AI & Robotics',    'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 70, 'code' => 1, 'c_lang' => 30, 'name' => '인공지능과 로봇',   'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 70, 'code' => 2, 'c_lang' => 10, 'name' => 'Software',         'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 70, 'code' => 2, 'c_lang' => 30, 'name' => '소프트웨어',        'use_yn' => 'Y'],

            // Art (c_genre: 80)
            ['inst' => 1, 'c_genre' => 80, 'code' => 1, 'c_lang' => 10, 'name' => 'Painting',         'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 80, 'code' => 1, 'c_lang' => 30, 'name' => '회화',             'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 80, 'code' => 2, 'c_lang' => 10, 'name' => 'Sculpture',        'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 80, 'code' => 2, 'c_lang' => 30, 'name' => '조각',             'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 80, 'code' => 3, 'c_lang' => 10, 'name' => 'Music',            'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 80, 'code' => 3, 'c_lang' => 30, 'name' => '음악',             'use_yn' => 'Y'],

            // Philosophy (c_genre: 90)
            ['inst' => 1, 'c_genre' => 90, 'code' => 1, 'c_lang' => 10, 'name' => 'Ethics',           'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 90, 'code' => 1, 'c_lang' => 30, 'name' => '윤리학',           'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 90, 'code' => 2, 'c_lang' => 10, 'name' => 'Logic',            'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 90, 'code' => 2, 'c_lang' => 30, 'name' => '논리학',           'use_yn' => 'Y'],

            // Sociology (c_genre: 100)
            ['inst' => 1, 'c_genre' => 100, 'code' => 1, 'c_lang' => 10, 'name' => 'Social Theory',   'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 100, 'code' => 1, 'c_lang' => 30, 'name' => '사회 이론',       'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 100, 'code' => 2, 'c_lang' => 10, 'name' => 'Social Issues',   'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 100, 'code' => 2, 'c_lang' => 30, 'name' => '사회 문제',       'use_yn' => 'Y'],

            // Hobby (c_genre: 110)
            ['inst' => 1, 'c_genre' => 110, 'code' => 1, 'c_lang' => 10, 'name' => 'Gardening',        'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 110, 'code' => 1, 'c_lang' => 30, 'name' => '원예',             'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 110, 'code' => 2, 'c_lang' => 10, 'name' => 'Cooking',          'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 110, 'code' => 2, 'c_lang' => 30, 'name' => '요리',             'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 110, 'code' => 3, 'c_lang' => 10, 'name' => 'Photography',      'use_yn' => 'Y'],
            ['inst' => 1, 'c_genre' => 110, 'code' => 3, 'c_lang' => 30, 'name' => '사진',           'use_yn' => 'Y'],
        ]);


        DB::table('code_c_rent_status')->delete();

        DB::table('code_c_rent_status')->insert([
            ['inst' => 1, 'code' => 10, 'c_lang' => 10, 'name' => 'Rented',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 10, 'c_lang' => 30, 'name' => '대여중',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 10, 'name' => 'Returned',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 30, 'name' => '반납',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 30, 'c_lang' => 10, 'name' => 'Damaged',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 30, 'c_lang' => 30, 'name' => '손상',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 40, 'c_lang' => 10, 'name' => 'Lost',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 40, 'c_lang' => 30, 'name' => '분실',   'use_yn' => 'Y'],
        ]);


        DB::table('code_c_rstatus')->delete();

        DB::table('code_c_rstatus')->insert([
            ['inst' => 1, 'code' => 10, 'c_lang' => 10, 'name' => 'Available',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 10, 'c_lang' => 30, 'name' => '대여가능',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 10, 'name' => 'Reserved',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 30, 'name' => '예약됨',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 30, 'c_lang' => 10, 'name' => 'Available (Damaged)',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 30, 'c_lang' => 30, 'name' => '대여가능 (손상)',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 40, 'c_lang' => 10, 'name' => 'Damaged',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 40, 'c_lang' => 30, 'name' => '손상',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 50, 'c_lang' => 10, 'name' => 'Lost',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 50, 'c_lang' => 30, 'name' => '분실',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 60, 'c_lang' => 10, 'name' => 'Missing',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 60, 'c_lang' => 30, 'name' => '소재불문명',   'use_yn' => 'Y'],
        ]);

        DB::table('code_c_grade')->delete();

        DB::table('code_c_grade')->insert([
            ['inst' => 1, 'code' => 10, 'c_lang' => 10, 'name' => 'Pre-Kinder',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 10, 'c_lang' => 30, 'name' => '유아',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 10, 'name' => 'Kinder',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 30, 'name' => '유치원',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 30, 'c_lang' => 10, 'name' => 'Elementary School',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 30, 'c_lang' => 30, 'name' => '초등학교',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 40, 'c_lang' => 10, 'name' => 'Middle School',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 40, 'c_lang' => 30, 'name' => '중등학교',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 50, 'c_lang' => 10, 'name' => 'High SChool',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 50, 'c_lang' => 30, 'name' => '고등학교',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 60, 'c_lang' => 10, 'name' => 'College',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 60, 'c_lang' => 30, 'name' => '대학',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 70, 'c_lang' => 10, 'name' => 'Graduate School',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 70, 'c_lang' => 30, 'name' => '대학원',   'use_yn' => 'Y'],
        ]);

        DB::table('code_c_code_set')->delete();

        DB::table('code_c_code_set')->insert([
            ['inst' => 1, 'code' => 10, 'c_lang' => 10, 'code_name' => 'code_c_genre',   'name' => 'Genre',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 10, 'c_lang' => 30, 'code_name' => 'code_c_genre',   'name' => '장르',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 10, 'code_name' => 'code_c_grade',   'name' => 'Grade',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 20, 'c_lang' => 30, 'code_name' => 'code_c_grade',   'name' => '학년',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 30, 'c_lang' => 10, 'code_name' => 'code_c_category',   'name' => 'Category',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 30, 'c_lang' => 30, 'code_name' => 'code_c_category',   'name' => '카테고리',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 40, 'c_lang' => 10, 'code_name' => 'code_c_category2',   'name' => 'Category2',   'use_yn' => 'Y'],
            ['inst' => 1, 'code' => 40, 'c_lang' => 30, 'code_name' => 'code_c_category2',   'name' => '카테고리2',   'use_yn' => 'Y'],
        ]);
    }
}
