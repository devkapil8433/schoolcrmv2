/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    './application/views/layout/student/**/*.php',
    './application/views/userrole/**/*.php',
    './application/views/authentication/**/*.php',
    './application/views/profile/student.php',
    './application/views/profile/parent.php',
    './application/views/profile/password_change.php',
    './application/views/communication/**/*.php',
    './application/views/onlineexam/take.php',
    './application/views/onlineexam/ajax_take.php',
    './application/views/onlineexam/student_result.php',
    './assets/student-portal/js/**/*.js',
    './assets/js/app.js',
    './assets/js/app.fn.js',
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'system-ui', 'sans-serif'],
      },
      colors: {
        primary: {
          DEFAULT: 'var(--portal-primary, #4f46e5)',
          50: 'var(--portal-primary-50, #eef2ff)',
          100: 'var(--portal-primary-100, #e0e7ff)',
          600: 'var(--portal-primary, #4f46e5)',
          700: 'var(--portal-primary-dark, #4338ca)',
        },
      },
    },
  },
  plugins: [
    require('@tailwindcss/forms'),
    require('@tailwindcss/typography'),
  ],
};
