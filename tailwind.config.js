/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './resources/views/**/*.blade.php',
    './resources/js/**/*.{vue,js,jsx,ts,tsx}',
  ],
  theme: {
    extend: {
      colors: {
        // T019: Design system colors from design-system.css
        // Orange - Primary action color
        orange: {
          primary: '#FF6B35',
          light: '#FF8C5A',
          dark: '#E55A2B',
          glow: 'rgba(255, 107, 53, 0.4)',
          DEFAULT: '#FF6B35',
        },
        // Blue - Secondary action color
        blue: {
          primary: '#2563EB',
          light: '#3B82F6',
          dark: '#1D4ED8',
          glow: 'rgba(37, 99, 235, 0.4)',
          DEFAULT: '#2563EB',
        },
        // Dark theme colors
        dark: {
          primary: '#0A0A0A',
          secondary: '#1A1A1A',
          tertiary: '#2A2A2A',
          card: '#151515',
        },
        // Keep existing colors for backward compatibility
        // Primary brand color (indigo)
        primary: {
          50: '#f0f4ff',
          100: '#e0e7ff',
          200: '#c7d2fe',
          300: '#a5b4fc',
          400: '#818cf8',
          500: '#6366f1',
          600: '#4f46e5',
          700: '#4338ca',
          800: '#3730a3',
          900: '#312e81',
        },
        // Semantic colors for authentication
        success: {
          50: '#f0fdf4',
          500: '#22c55e',
          700: '#15803d',
        },
        error: {
          50: '#fef2f2',
          500: '#ef4444',
          700: '#b91c1c',
        },
        warning: {
          50: '#fffbeb',
          500: '#f59e0b',
          700: '#b45309',
        },
      },
      typography: {
        // Page headings (h1)
        pageHeading: {
          fontSize: '1.875rem',
          fontWeight: '700',
          lineHeight: '2.25rem',
        },
        // Section headings (h2)
        sectionHeading: {
          fontSize: '1.5rem',
          fontWeight: '600',
          lineHeight: '2rem',
        },
        // Form labels
        label: {
          fontSize: '0.875rem',
          fontWeight: '500',
          lineHeight: '1.25rem',
        },
        // Form input text
        input: {
          fontSize: '1rem',
          fontWeight: '400',
          lineHeight: '1.5rem',
        },
        // Helper and error text
        caption: {
          fontSize: '0.75rem',
          fontWeight: '400',
          lineHeight: '1rem',
        },
      },
      spacing: {
        // Form field spacing
        formGap: '1.25rem',
        formSection: '1.5rem',
      },
      borderRadius: {
        // Button and input border radius
        form: '0.5rem',
      },
      boxShadow: {
        // Card shadow for auth pages
        card: '0 10px 25px -5px rgba(0, 0, 0, 0.1)',
      },
      animation: {
        // Loading spinner animation
        'spin-fast': 'spin 0.6s linear infinite',
      },
      minHeight: {
        // Accessible touch target size (WCAG)
        touch: '2.75rem',
      },
      minWidth: {
        // Accessible touch target size (WCAG)
        touch: '2.75rem',
      },
    },
  },
  plugins: [],
}
