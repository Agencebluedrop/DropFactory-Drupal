/** @type {import('tailwindcss').Config} */

const typography = require('@tailwindcss/typography')({
  modifiers: ['md', 'lg'],
  className: 'tw-content',
});
module.exports = {
  content: [
    "./assets/**/*.js",
    "./templates/**/**/*.html.twig",
  ],
  theme: {
    screens: {
      sm: '480px',
      md: '768px',
      lg: '1024px',
      xl: '1248px',
      xxl: '1440px',
    },
    colors: {
      white: '#ffffff',
      black: '#000000',
      success: '#0AC275',
      error: '#D12E0B',
      warning: '#fdd230',
      pending: '#FC6A03',
      transparent: 'transparent',
      brand: {
        primary: '#0B45D9',
        secondary: '#02282E',
        tertiary: '#FCF1E7'
      },
      secondary: {
        900: '#292929',
        1000: '#011417'
      },
      neutral: {
        50: '#F6F6F7',
        100: '#ECEEEE',
        200: '#E5E5E5',
        300: '#E5E9EA'
      },
      primary: {
        800: '#051D5B',
      },
      cyan: {
        800: '#175E73',
        1000: '#051B24'
      }
    },
    typography: (theme) => ({
      DEFAULT: {
        css: {
          color: theme('colors.brand.secondary'),
          fontSize: '1rem',
          lineHeight: '1.75rem',
          'h1, h2, h3, h4': {
            fontWeight: 'bold',
          },
          'h1, .h1': {
            fontSize: '2rem', //32px
            lineHeight: '2.5rem', //40px
          },
          'h2, .h2': {
            fontSize: '1.5rem', //24px
            lineHeight: '2rem', //32px
          },
          'h3, .h3': {
            fontSize: '1.25rem', //20px
            lineHeight: '1.75rem', //28px
          },
          'h4, .h4': {
            fontSize: '1.125rem', //18px
            lineHeight: '1.625rem', //26px
            fontFeatureSettings: "'ss03' on",
            fontWeight: '800'
          },
          'p': {
            '+ p' :{
              marginTop: '1.5rem'
            }
          }
        }
      },
      lg: {
        css: {
          'h1, .h1': {
            fontSize: '3.5rem', //56px
            lineHeight: '4rem', //64px
          },
          'h2, .h2': {
            fontSize: '2.5rem', //40px
            lineHeight: '4rem', //64px
          },
          'h3, .h3': {
            fontSize: '1.5rem', //24px
            lineHeight: '2.5rem', //40px
          },
          'h4, .h4': {
            fontSize: '1.25rem', //20px
            lineHeight: '2rem', //32px
          },
        }
      }
    }),
    extend: {
      fontFamily: {
        sans: ['Plus Jakarta Sans', 'sans-serif'],
      },
      fontWeight: {
        light: '300',
        normal: '400',
        medium: '500',
        semibold: '600',
        bold: '700',
      },
      maxWidth: {
        none: 'none',
        container: '73rem', //1168px
      },
      boxShadow: (theme) => ({
        'custom': '4px 4px 28px 0px rgba(2, 40, 46, 0.08)',
        'badge': '8px 8px 24px 0px rgba(0, 0, 0, 0.08);'
      }),
      backgroundImage: (theme) => ({
        'checkbox-checked': "url('/assets/images/check.svg')",
        'chevron-down': "url('/assets/images/chevron-down.svg')",
      }),
    },
  },
  plugins: [
    typography,
    function({ addVariant }) {
      addVariant('footer', '.footer &');
      addVariant('header', '.header &');
      addVariant('child', '& > *');
      addVariant('error-item', '& ul > li');
      addVariant('icon-path', '& path');
      addVariant('choices', '& .choices');
      addVariant('choices-inner', '& .choices__inner');
      addVariant('choices-inner-single','& .choices__list--single');
      addVariant('choices-arrow', '& .choices[data-type*=select-one]::after');
      addVariant('choices-inner-dropdown', '& .choices__list--dropdown');
      addVariant('choices-open-arrow', '& .choices.is-open::after');
      addVariant('choices-items', '& .choices__list--dropdown .choices__item');
      addVariant('choices-input', '& .choices__input');
      addVariant('choices-items-hovered', '& .choices__list--dropdown .choices__item--selectable.is-highlighted, .choices__list[aria-expanded] .choices__item--selectable.is-highlighted');
    },
  ],
}
