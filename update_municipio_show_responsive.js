import fs from 'fs';
import path from 'path';

const rootDir = process.cwd();
const showPath = path.join(rootDir, 'resources', 'views', 'livewire', 'municipio-show.blade.php');

if (fs.existsSync(showPath)) {
    let content = fs.readFileSync(showPath, 'utf8');

    // 1. Padding on mobile
    content = content.replace(
        '<div class="space-y-6 px-4 py-8 md:px-6 lg:px-8">',
        '<div class="space-y-6 px-3 sm:px-6 lg:px-8 py-4 sm:py-8">'
    );

    // 2. Header card padding on mobile
    content = content.replace(
        '<div class="rounded-xl border border-zinc-200 bg-white p-5 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">',
        '<div class="rounded-2xl border border-zinc-200 bg-white p-4 sm:p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">'
    );

    // 3. Stats grid on mobile: 2 cols on mobile, 3 on sm, 6 on lg
    content = content.replace(
        '<div class="grid grid-cols-3 gap-2 sm:grid-cols-6">',
        '<div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-2 w-full lg:w-auto">'
    );

    // 4. Module cards layout on small screens: stack smoothly
    content = content.replace(
        '<div class="flex items-start justify-between gap-4">',
        '<div class="flex flex-col sm:flex-row sm:items-start justify-between gap-3 sm:gap-4">'
    );

    fs.writeFileSync(showPath, content, 'utf8');
    console.log('municipio-show.blade.php updated successfully');
}
