import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './app/Http/Controllers/**/*.php',
    ],

    // Safelist: class yang dipakai di Alpine expression atau string dinamis
    // Tailwind tidak bisa scan ini dari template, jadi harus didaftarkan manual
    safelist: [
        // Sidebar width
        'w-64', 'w-[68px]',
        // Main padding (bergeser sesuai sidebar)
        'lg:pl-64', 'lg:pl-[68px]',
        // Kondisi aset
        'bg-green-100', 'text-green-800',
        'bg-yellow-100', 'text-yellow-800',
        'bg-orange-100', 'text-orange-800',
        'bg-red-100', 'text-red-800',
        'bg-gray-100', 'text-gray-600',
        // Status badges
        'bg-emerald-100', 'text-emerald-700', 'text-emerald-800',
        'bg-amber-100', 'text-amber-700', 'text-amber-800',
        'bg-slate-100', 'text-slate-600', 'text-slate-700',
        'bg-blue-100', 'text-blue-700', 'text-blue-800',
        'bg-purple-100', 'text-purple-700', 'text-purple-800',
        'bg-sky-100', 'text-sky-700', 'text-sky-800',
        'bg-teal-100', 'text-teal-700', 'text-teal-800',
        'bg-red-50', 'bg-amber-50', 'bg-emerald-50', 'bg-sky-50',
        'bg-purple-50', 'bg-slate-50', 'bg-blue-50', 'bg-teal-50',
        'border-red-100', 'border-amber-100', 'border-emerald-100',
        'border-sky-100', 'border-purple-100', 'border-slate-200',
        'border-blue-100', 'border-teal-100',
        'text-red-600', 'text-amber-600', 'text-emerald-600',
        'text-sky-600', 'text-purple-600', 'text-slate-500',
        'text-blue-600', 'text-teal-600',
        // Tipe unit kerja badges
        'bg-purple-100', 'text-purple-800',
        // Icon colors
        'text-red-500', 'text-amber-500', 'text-emerald-500',
        'text-sky-500', 'text-purple-500',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
