import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    darkMode: ['class'],
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.tsx',
    ],

    theme: {
        container: {
            center: true,
            padding: {
                DEFAULT: '1rem',
                sm: '2rem',
                lg: '4rem',
                xl: '5rem',
                '2xl': '6rem',
            },
        },
    	extend: {
    		fontFamily: {
    			sans: [
    				// 'Figtree',
                    'Inter',
                    ...defaultTheme.fontFamily.sans
                ]
    		},
    		colors: {
    			primary: {
    				'100': '#CDFBDA',
    				'200': '#9DF8BF',
    				'300': '#69EAA6',
    				'400': '#43D596',
    				'500': '#10B981',
    				'600': '#0B9F7C',
    				'700': '#088574',
    				'800': '#056B67',
    				'900': '#035358',
    				DEFAULT: 'hsl(var(--primary))',
    				foreground: 'hsl(var(--primary-foreground))'
    			},
    			secondary: {
    				'100': '#F0FDF4',
    				'200': '#DCFCE7',
    				'300': '#BBF7D0',
    				'400': '#86EFAC',
    				'500': '#4ADE80',
    				'600': '#22C55E',
    				'700': '#16A34A',
    				'800': '#15803D',
    				'900': '#166534',
    				DEFAULT: 'hsl(var(--secondary))',
    				foreground: 'hsl(var(--secondary-foreground))'
    			},
    			background: 'hsl(var(--background))',
    			foreground: 'hsl(var(--foreground))',
    			card: {
    				DEFAULT: 'hsl(var(--card))',
    				foreground: 'hsl(var(--card-foreground))'
    			},
    			popover: {
    				DEFAULT: 'hsl(var(--popover))',
    				foreground: 'hsl(var(--popover-foreground))'
    			},
    			muted: {
    				DEFAULT: 'hsl(var(--muted))',
    				foreground: 'hsl(var(--muted-foreground))'
    			},
    			accent: {
    				DEFAULT: 'hsl(var(--accent))',
    				foreground: 'hsl(var(--accent-foreground))'
    			},
    			destructive: {
    				DEFAULT: 'hsl(var(--destructive))',
    				foreground: 'hsl(var(--destructive-foreground))'
    			},
    			border: 'hsl(var(--border))',
    			input: 'hsl(var(--input))',
    			ring: 'hsl(var(--ring))',
    			chart: {
    				'1': 'hsl(var(--chart-1))',
    				'2': 'hsl(var(--chart-2))',
    				'3': 'hsl(var(--chart-3))',
    				'4': 'hsl(var(--chart-4))',
    				'5': 'hsl(var(--chart-5))'
    			}
    		},
    		borderRadius: {
    			lg: 'var(--radius)',
    			md: 'calc(var(--radius) - 2px)',
    			sm: 'calc(var(--radius) - 4px)'
    		},
    		backgroundSize: {
    			'200': '200% 200%',
    		},
    		animation: {
    			'gradient-x': 'gradient-x 3s ease infinite',
    		},
    		keyframes: {
    			'gradient-x': {
    				'0%, 100%': {
    					'background-position': '0% 50%'
    				},
    				'50%': {
    					'background-position': '100% 50%'
    				}
    			}
    		}
    	}
    },

    plugins: [forms, require("tailwindcss-animate")],
};
