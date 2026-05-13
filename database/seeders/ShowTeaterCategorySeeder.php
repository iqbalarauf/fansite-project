<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ShowTeaterCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('show_teater_categories')->truncate();

        $now = now();

        $categories = [
            ['id' => 1, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Aturan Anti Cinta', 'jp_name' => 'Renai Kinshi Jourei', 'created_at' => $now],
            ['id' => 2, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'BANZAI JKT48', 'jp_name' => 'BANZAI JKT48', 'created_at' => $now],
            ['id' => 3, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Cara Meminum Ramune', 'jp_name' => 'Ramune no Nomikata', 'created_at' => $now],
            ['id' => 4, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Fly! Team T', 'jp_name' => '', 'created_at' => $now],
            ['id' => 5, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Gadis Gadis Remaja', 'jp_name' => 'Seishun Girls', 'created_at' => $now],
            ['id' => 6, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Matahari Milikku', 'jp_name' => 'Boku no Taiyou', 'created_at' => $now],
            ['id' => 7, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Pajama Drive', 'jp_name' => 'Pajama Drive', 'created_at' => $now],
            ['id' => 8, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Pertaruhan Cinta', 'jp_name' => '', 'created_at' => $now],
            ['id' => 9, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Sambil Menggandeng Erat Tanganku', 'jp_name' => 'Te wo Tsunaginagara', 'created_at' => $now],
            ['id' => 10, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Tunas di Balik Seragam', 'jp_name' => 'Seifuku no Me', 'created_at' => $now],
            ['id' => 11, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'KIRA-KIRA Girls', 'jp_name' => 'KIRA-KIRA Girls', 'created_at' => $now],
            ['id' => 12, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'ITADAKI♥LOVE', 'jp_name' => '', 'created_at' => $now],
            ['id' => 13, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'DREAM BAKUDAN', 'jp_name' => '', 'created_at' => $now],
            ['id' => 14, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'PASSION 200%', 'jp_name' => '', 'created_at' => $now],
            ['id' => 15, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Sekarang Sedang Jatuh Cinta', 'jp_name' => 'Tadaima Renaichuu', 'created_at' => $now],
            ['id' => 16, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Fajar Sang Idola', 'jp_name' => 'Idol no Yoake', 'created_at' => $now],
            ['id' => 17, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Saka Agari', 'jp_name' => 'Saka Agari', 'created_at' => $now],
            ['id' => 18, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Dewi Teater', 'jp_name' => 'Theater no Megami', 'created_at' => $now],
            ['id' => 19, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Romansa Sang Gadis', 'jp_name' => '', 'created_at' => $now],
            ['id' => 20, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'B•E•L•I•E•V•E', 'jp_name' => '', 'created_at' => $now],
            ['id' => 21, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Demi Seseorang', 'jp_name' => 'Dareka no Tame ni', 'created_at' => $now],
            ['id' => 22, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Bel Terakhir Telah Berbunyi', 'jp_name' => 'Saishuu Bell ga Naru', 'created_at' => $now],
            ['id' => 23, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Ingin Bertemu', 'jp_name' => 'Aitakatta', 'created_at' => $now],
            ['id' => 24, 'type' => 'setlist', 'setlist_id' => null, 'name' => 'Bunga Matahari', 'jp_name' => 'Himawarigumi', 'created_at' => $now],

            ['id' => 25, 'type' => 'unit_song', 'setlist_id' => 1, 'name' => 'Malaikat Hitam', 'jp_name' => 'Kuroi Tenshi', 'created_at' => $now],
            ['id' => 26, 'type' => 'unit_song', 'setlist_id' => 1, 'name' => 'Mawar Natal Musim Panas', 'jp_name' => 'Manatsu no Christmas Rose', 'created_at' => $now],
            ['id' => 27, 'type' => 'unit_song', 'setlist_id' => 1, 'name' => 'Aturan Anti Cinta', 'jp_name' => 'Renai Kinshi Jourei', 'created_at' => $now],
            ['id' => 28, 'type' => 'unit_song', 'setlist_id' => 1, 'name' => 'Virus Tipe Hati', 'jp_name' => 'Heart Gata Virus', 'created_at' => $now],
            ['id' => 29, 'type' => 'unit_song', 'setlist_id' => 1, 'name' => 'Tsundere!', 'jp_name' => 'Tsundere!', 'created_at' => $now],

            ['id' => 30, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Finland Miracle', 'jp_name' => '', 'created_at' => $now],
            ['id' => 31, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Joan of Arc di Dalam Cermin', 'jp_name' => 'Kagami no Naka no Jean Da Arc', 'created_at' => $now],
            ['id' => 32, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Ajak Aku Pergi ke Wimbledon', 'jp_name' => 'Wimbledon e Tsureteitte', 'created_at' => $now],
            ['id' => 33, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Putik, Benang Sari, dan Kupu-Kupu Malam', 'jp_name' => 'Oshibe to Meshibe to Yoru no Chouchou', 'created_at' => $now],
            ['id' => 34, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Pesawat Kertas 365 Hari (Accoustic)', 'jp_name' => '365nichi no Kamikihouki', 'created_at' => $now],
            ['id' => 35, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Cross', 'jp_name' => '', 'created_at' => $now],
            ['id' => 36, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Prinsip Kesucian Hati', 'jp_name' => 'Junjou Shugi', 'created_at' => $now],
            ['id' => 37, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Candy', 'jp_name' => '', 'created_at' => $now],
            ['id' => 38, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Glory Days', 'jp_name' => '', 'created_at' => $now],
            ['id' => 39, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Perbuatan Angin Malam', 'jp_name' => 'Yokaze no Shizawa', 'created_at' => $now],
            ['id' => 40, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Pencuri Cinta Pertama', 'jp_name' => 'Hatsukoi Dorobou', 'created_at' => $now],
            ['id' => 41, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Maafkan Permataku', 'jp_name' => 'Gomen ne Jewel', 'created_at' => $now],
            ['id' => 42, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Jika Aku Dipelukmu', 'jp_name' => 'Dakishimerareta', 'created_at' => $now],
            ['id' => 43, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Pulang Kampung', 'jp_name' => 'Kikyou', 'created_at' => $now],
            ['id' => 44, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Sang Pianis Hujan', 'jp_name' => 'Ame no Pianist', 'created_at' => $now],
            ['id' => 45, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Bird', 'jp_name' => '', 'created_at' => $now],
            ['id' => 46, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Cinta Pertama, Selamat Siang', 'jp_name' => 'Hatsukoi yo, Konnichiwa', 'created_at' => $now],
            ['id' => 47, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Sampai Musim Semi Tiba', 'jp_name' => 'Haru ga Kuru Made', 'created_at' => $now],
            ['id' => 48, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Ekor Malaikat', 'jp_name' => 'Tenshi no Shippo', 'created_at' => $now],
            ['id' => 49, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Pada Malam yang Berbadai', 'jp_name' => 'Arashi no Yoru ni wa', 'created_at' => $now],
            ['id' => 50, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Gadis yang Celaka', 'jp_name' => 'Zannen Shoujo', 'created_at' => $now],
            ['id' => 51, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Mimpi Yang Hina', 'jp_name' => 'Gesu na Yume', 'created_at' => $now],
            ['id' => 52, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Bukan Alpukat', 'jp_name' => 'Avocado Janeshi', 'created_at' => $now],
            ['id' => 53, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Cinta Pertama di Jam 7 Lewat 12', 'jp_name' => '7ji 12fun no Hatsukoi', 'created_at' => $now],
            ['id' => 54, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Khayalan', 'jp_name' => 'Shinkirou', 'created_at' => $now],
            ['id' => 55, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Cinta yang Tulus, Crescendo', 'jp_name' => 'Junai no Crescendo', 'created_at' => $now],
            ['id' => 56, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Natasha Yang Kucinta', 'jp_name' => 'Itoshiki Natasha', 'created_at' => $now],
            ['id' => 57, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Berikanlah Coklat Dengan Bibir', 'jp_name' => 'Kuchi Utsushi no Chocolate', 'created_at' => $now],
            ['id' => 58, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Si Bintang Jatuh yang Egois', 'jp_name' => 'Wagamama na Nagareboshi', 'created_at' => $now],
            ['id' => 59, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Air Mata Perasaan yang Tak Tersampaikan', 'jp_name' => 'Temodemo no Namida', 'created_at' => $now],
            ['id' => 60, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Bunga Matahari', 'jp_name' => 'Himawari', 'created_at' => $now],
            ['id' => 61, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Dua Orang yang Terlarang', 'jp_name' => 'Kinjirareta Futari', 'created_at' => $now],
            ['id' => 62, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Rider', 'jp_name' => '', 'created_at' => $now],
            ['id' => 63, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Faint', 'jp_name' => '', 'created_at' => $now],
            ['id' => 64, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Pertahanan dari Cinta', 'jp_name' => 'Itoshisa no Defense', 'created_at' => $now],
            ['id' => 65, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Berandalan di Surga', 'jp_name' => 'Tengoku Yarou', 'created_at' => $now],
            ['id' => 66, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Jatuhkan dengan Kiss Bye!', 'jp_name' => 'Nage Kiss de Uchi Otose!', 'created_at' => $now],
            ['id' => 67, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Blue Rose', 'jp_name' => '', 'created_at' => $now],
            ['id' => 68, 'type' => 'unit_song', 'setlist_id' => 2, 'name' => 'Return Match', 'jp_name' => '', 'created_at' => $now],

            ['id' => 69, 'type' => 'unit_song', 'setlist_id' => 3, 'name' => 'Cross', 'jp_name' => '', 'created_at' => $now],
            ['id' => 70, 'type' => 'unit_song', 'setlist_id' => 3, 'name' => 'Finland Miracle', 'jp_name' => '', 'created_at' => $now],
            ['id' => 71, 'type' => 'unit_song', 'setlist_id' => 3, 'name' => 'Nice to Meet You!', 'jp_name' => '', 'created_at' => $now],
            ['id' => 72, 'type' => 'unit_song', 'setlist_id' => 3, 'name' => 'Menatapmu Sayonara', 'jp_name' => 'Manazashi Sayonara', 'created_at' => $now],
            ['id' => 73, 'type' => 'unit_song', 'setlist_id' => 3, 'name' => 'Burung Unta si Pembohong', 'jp_name' => 'Usotsuki na Dachou', 'created_at' => $now],

            ['id' => 74, 'type' => 'unit_song', 'setlist_id' => 4, 'name' => 'Dash Cinta Pertama', 'jp_name' => 'Hatsukoi Dash', 'created_at' => $now],
            ['id' => 75, 'type' => 'unit_song', 'setlist_id' => 4, 'name' => 'Relax!', 'jp_name' => '', 'created_at' => $now],
            ['id' => 76, 'type' => 'unit_song', 'setlist_id' => 4, 'name' => 'Pada Malam yang Berbadai', 'jp_name' => 'Arashi no Yoru ni wa', 'created_at' => $now],
            ['id' => 77, 'type' => 'unit_song', 'setlist_id' => 4, 'name' => 'Kaca Berbentuk I LOVE YOU', 'jp_name' => 'Glass no I LOVE YOU', 'created_at' => $now],
            ['id' => 78, 'type' => 'unit_song', 'setlist_id' => 4, 'name' => 'Pundak Kanan', 'jp_name' => 'Migikata', 'created_at' => $now],

            ['id' => 79, 'type' => 'unit_song', 'setlist_id' => 5, 'name' => 'Blue Rose', 'jp_name' => '', 'created_at' => $now],
            ['id' => 80, 'type' => 'unit_song', 'setlist_id' => 5, 'name' => 'Dua Orang yang Terlarang', 'jp_name' => 'Kinjirareta Futari', 'created_at' => $now],
            ['id' => 81, 'type' => 'unit_song', 'setlist_id' => 5, 'name' => 'Kebun Binatang Saat Hujan', 'jp_name' => 'Ame no Doubutsuen', 'created_at' => $now],

            ['id' => 82, 'type' => 'unit_song', 'setlist_id' => 6, 'name' => 'Bunga Matahari', 'jp_name' => 'Himawari', 'created_at' => $now],
            ['id' => 83, 'type' => 'unit_song', 'setlist_id' => 6, 'name' => 'Pertahanan dari Cinta', 'jp_name' => 'Itoshisa no Defense', 'created_at' => $now],
            ['id' => 84, 'type' => 'unit_song', 'setlist_id' => 6, 'name' => 'Aku, Juliette, dan Jet Coaster', 'jp_name' => 'Boku to Juliet to Jet Coaster', 'created_at' => $now],
            ['id' => 85, 'type' => 'unit_song', 'setlist_id' => 6, 'name' => 'Cinta Higurashi', 'jp_name' => 'Higurashi no Koi', 'created_at' => $now],
            ['id' => 86, 'type' => 'unit_song', 'setlist_id' => 6, 'name' => 'Jangan Panggil Aku Idol', 'jp_name' => 'Idol Nante Yobanaide', 'created_at' => $now],

            ['id' => 87, 'type' => 'unit_song', 'setlist_id' => 7, 'name' => 'Prinsip Kesucian Hati', 'jp_name' => 'Junjou Shugi', 'created_at' => $now],
            ['id' => 88, 'type' => 'unit_song', 'setlist_id' => 7, 'name' => 'Pajama Drive', 'jp_name' => '', 'created_at' => $now],
            ['id' => 89, 'type' => 'unit_song', 'setlist_id' => 7, 'name' => 'Ekor Malaikat', 'jp_name' => 'Tenshi no Shippo', 'created_at' => $now],
            ['id' => 90, 'type' => 'unit_song', 'setlist_id' => 7, 'name' => 'Air Mata Perasaan yang Tak Tersampaikan', 'jp_name' => 'Temodemo no Namida', 'created_at' => $now],
            ['id' => 91, 'type' => 'unit_song', 'setlist_id' => 7, 'name' => 'Joan of Arc di Dalam Cermin', 'jp_name' => 'Kagami no Naka no Jean da Arc', 'created_at' => $now],

            ['id' => 92, 'type' => 'unit_song', 'setlist_id' => 8, 'name' => 'Dai Dai Dai', 'jp_name' => '', 'created_at' => $now],
            ['id' => 93, 'type' => 'unit_song', 'setlist_id' => 8, 'name' => 'Onyx', 'jp_name' => '', 'created_at' => $now],
            ['id' => 94, 'type' => 'unit_song', 'setlist_id' => 8, 'name' => 'Sahabat atau Cinta', 'jp_name' => '', 'created_at' => $now],
            ['id' => 95, 'type' => 'unit_song', 'setlist_id' => 8, 'name' => 'Percik Kecil', 'jp_name' => '', 'created_at' => $now],

            ['id' => 96, 'type' => 'unit_song', 'setlist_id' => 9, 'name' => 'Sang Pianis Hujan', 'jp_name' => 'Ame no Pianist', 'created_at' => $now],
            ['id' => 97, 'type' => 'unit_song', 'setlist_id' => 9, 'name' => 'Glory Days', 'jp_name' => '', 'created_at' => $now],
            ['id' => 98, 'type' => 'unit_song', 'setlist_id' => 9, 'name' => 'Barcode Hati Ini', 'jp_name' => 'Kono Mune no Barcode', 'created_at' => $now],
            ['id' => 99, 'type' => 'unit_song', 'setlist_id' => 9, 'name' => 'Ajak Aku Pergi Menuju ke Wimbledon', 'jp_name' => 'Wimbledon e Tsureteitte', 'created_at' => $now],
            ['id' => 100, 'type' => 'unit_song', 'setlist_id' => 9, 'name' => 'Keberadaan Coklat Itu', 'jp_name' => 'Choco no Yukue', 'created_at' => $now],

            ['id' => 101, 'type' => 'unit_song', 'setlist_id' => 10, 'name' => 'Kaleidoskop', 'jp_name' => 'Mangekyou', 'created_at' => $now],
            ['id' => 102, 'type' => 'unit_song', 'setlist_id' => 10, 'name' => 'Lebih dari Memori', 'jp_name' => 'Omoide Ijou', 'created_at' => $now],
            ['id' => 103, 'type' => 'unit_song', 'setlist_id' => 10, 'name' => 'Indra Keenam Seorang Gadis', 'jp_name' => 'Onna no Ko no Dairokkan', 'created_at' => $now],
            ['id' => 104, 'type' => 'unit_song', 'setlist_id' => 10, 'name' => 'Serigala dan Pride', 'jp_name' => 'Ookami to Pride', 'created_at' => $now],
            ['id' => 105, 'type' => 'unit_song', 'setlist_id' => 10, 'name' => 'Stasiun Daun Kering', 'jp_name' => 'Kareha no Station', 'created_at' => $now],
        ];

        DB::table('show_teater_categories')->insert($categories);
    }
}
