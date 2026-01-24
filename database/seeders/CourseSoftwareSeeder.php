<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\SoftwareDetail;
use Illuminate\Database\Seeder;

class CourseSoftwareSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * 
     * Menghubungkan mata kuliah dengan software yang dibutuhkan
     * Matching berdasarkan NAMA mata kuliah (case insensitive)
     */
    public function run(): void
    {
        // Mapping: course_name => [software_codes]
        $courseSoftware = [
            // Pemrograman
            'Algoritma dan Struktur Data' => ['VSCODE', 'GIT'],
            'Pemrograman Berorientasi Objek' => ['INTELLIJ', 'VSCODE', 'GIT'],
            'Pemrograman Web Lanjut' => ['VSCODE', 'XAMPP', 'NODEJS', 'POSTMAN', 'CHROME', 'GIT'],
            'Sistem Basis Data' => ['MYSQL', 'DBEAVER', 'PHPMYADMIN', 'XAMPP'],
            'Pemrograman Sisi Klien' => ['VSCODE', 'NODEJS', 'CHROME', 'FIREFOX', 'GIT'],
            'Pemrograman Sisi Server' => ['VSCODE', 'NODEJS', 'LARAGON', 'POSTMAN', 'GIT'],
            'Pemrograman Game' => ['UNITY', 'GODOT', 'VSCODE', 'BLENDER', 'GIT'],

            // Database
            'Manajemen Basis Data' => ['MYSQL', 'DBEAVER', 'PHPMYADMIN', 'EXCEL'],
            'Basis Data' => ['MYSQL', 'DBEAVER', 'PHPMYADMIN'],

            // DKV / Desain
            'Grafis Komputer' => ['PHOTOSHOP', 'ILLUSTRATOR', 'COREL'],
            'Desain Web' => ['FIGMA', 'PHOTOSHOP', 'VSCODE', 'CHROME'],
            'Reprografika' => ['ILLUSTRATOR', 'COREL', 'PHOTOSHOP'],
            'Proyek Desain Kemasan' => ['ILLUSTRATOR', 'COREL', 'PHOTOSHOP'],

            // 3D Modeling
            'Pemodelan 3D' => ['BLENDER', 'MAYA', '3DSMAX', 'ZBRUSH'],
            'Pemodelan 3D I' => ['BLENDER', 'MAYA', '3DSMAX'],

            // Animation
            'Grafis Bergerak' => ['ANIMATE', 'AFTEREFFECT', 'BLENDER'],
            'Grafika Gerak' => ['CINEMA4D', 'AFTEREFFECT', 'BLENDER'],
            'Proyek Animasi' => ['BLENDER', 'MAYA', 'AFTEREFFECT', 'ANIMATE'],
            'Animasi 2D I' => ['TOONBOOM', 'ANIMATE', 'CLIPSTUDIO'],
            'Animasi 3D I' => ['MAYA', 'BLENDER', '3DSMAX'],
            'Animasi 3D II' => ['MAYA', 'BLENDER', '3DSMAX', 'ZBRUSH'],
            'Ilustrasi' => ['CLIPSTUDIO', 'PHOTOSHOP', 'ILLUSTRATOR'],
            'Rigging 2D' => ['SPINE', 'DRAGONBONES', 'TOONBOOM'],

            // VFX
            'Efek Visual 2D' => ['AFTEREFFECT', 'TOONBOOM', 'ANIMATE'],
            'Efek Visual 3D' => ['AFTEREFFECT', 'BLENDER', 'MAYA'],

            // Video & Audio
            'Video Editing' => ['DAVINCI', 'AFTEREFFECT'],
            'Digital Storytelling' => ['DAVINCI', 'AFTEREFFECT', 'AUDITION', 'PHOTOSHOP'],
            'Tata Suara Pemutaran Film' => ['AUDITION', 'DAVINCI'],

            // AI
            'Kecerdasan Artifisial Kreatif' => ['PYTHON', 'JUPYTER', 'COMFYUI', 'STABLEDIFF'],

            // Proyek/Konten
            'Proyek Konten Kreatif' => ['PHOTOSHOP', 'ILLUSTRATOR', 'AFTEREFFECT'],
            'Multimedia' => ['FLASH', 'PHOTOSHOP', 'ANIMATE'],
            'Proyek Aplikasi Web I' => ['VSCODE', 'XAMPP', 'NODEJS', 'POSTMAN', 'GIT'],
            'Proyek Aplikasi Mobile II' => ['ANDROID_STUDIO', 'VSCODE', 'GIT', 'FIGMA'],
        ];

        $attached = 0;
        $skipped = 0;

        foreach ($courseSoftware as $courseName => $softwareCodes) {
            // Match by name (case insensitive)
            $courses = Course::whereRaw('LOWER(name) = ?', [strtolower($courseName)])->get();
            
            if ($courses->isEmpty()) {
                $this->command->warn("Course not found: {$courseName}");
                $skipped++;
                continue;
            }

            $softwareIds = SoftwareDetail::whereIn('code', $softwareCodes)->pluck('id')->toArray();
            
            if (empty($softwareIds)) {
                $this->command->warn("No software found for course: {$courseName}");
                $skipped++;
                continue;
            }

            // Attach to ALL courses with matching name (multiple prodi may have same course name)
            foreach ($courses as $course) {
                $course->software()->sync($softwareIds);
                $attached++;
                $this->command->info("Attached " . count($softwareIds) . " software to: [{$course->code}] {$course->name}");
            }
        }

        $this->command->info("CourseSoftware seeder completed: {$attached} courses linked, {$skipped} skipped.");
    }
}
