<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Unifiedtransform</title>

        <link rel="shortcut icon" href="{{asset('favicon_io/favicon.ico')}}">
        <link rel="shortcut icon" sizes="16x16" href="{{asset('favicon_io/favicon-16x16.png')}}">
        <link rel="shortcut icon" sizes="32x32" href="{{asset('favicon_io/favicon-32x32.png')}}">
        <link rel="apple-touch-icon" href="{{asset('favicon_io/apple-touch-icon.png')}}">
        <link rel="icon" href="{{asset('favicon_io/android-chrome-192x192.png')}}" sizes="192x192">
        <link rel="icon" href="{{asset('favicon_io/android-chrome-512x512.png')}}" sizes="512x512">

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;600;700&display=swap" rel="stylesheet">

        <!-- Styles -->
        <style>
            /*! normalize.css v8.0.1 | MIT License | github.com/necolas/normalize.css */html{line-height:1.15;-webkit-text-size-adjust:100%}body{margin:0}a{background-color:transparent}[hidden]{display:none}html{font-family:system-ui,-apple-system,BlinkMacSystemFont,Segoe UI,Roboto,Helvetica Neue,Arial,Noto Sans,sans-serif,Apple Color Emoji,Segoe UI Emoji,Segoe UI Symbol,Noto Color Emoji;line-height:1.5}*,:after,:before{box-sizing:border-box;border:0 solid #e2e8f0}a{color:inherit;text-decoration:inherit}svg,video{display:block;vertical-align:middle}video{max-width:100%;height:auto}.bg-white{--bg-opacity:1;background-color:#fff;background-color:rgba(255,255,255,var(--bg-opacity))}.bg-gray-100{--bg-opacity:1;background-color:#f7fafc;background-color:rgba(247,250,252,var(--bg-opacity))}.border-gray-200{--border-opacity:1;border-color:#edf2f7;border-color:rgba(237,242,247,var(--border-opacity))}.border-t{border-top-width:1px}.flex{display:flex}.grid{display:grid}.hidden{display:none}.items-center{align-items:center}.justify-center{justify-content:center}.font-semibold{font-weight:600}.h-5{height:1.25rem}.h-8{height:2rem}.h-16{height:4rem}.text-sm{font-size:.875rem}.text-lg{font-size:1.125rem}.leading-7{line-height:1.75rem}.mx-auto{margin-left:auto;margin-right:auto}.ml-1{margin-left:.25rem}.mt-2{margin-top:.5rem}.mr-2{margin-right:.5rem}.ml-2{margin-left:.5rem}.mt-4{margin-top:1rem}.ml-4{margin-left:1rem}.mt-8{margin-top:2rem}.ml-12{margin-left:3rem}.-mt-px{margin-top:-1px}.max-w-6xl{max-width:72rem}.min-h-screen{min-height:100vh}.overflow-hidden{overflow:hidden}.p-6{padding:1.5rem}.py-4{padding-top:1rem;padding-bottom:1rem}.px-6{padding-left:1.5rem;padding-right:1.5rem}.pt-8{padding-top:2rem}.fixed{position:fixed}.relative{position:relative}.top-0{top:0}.right-0{right:0}.shadow{box-shadow:0 1px 3px 0 rgba(0,0,0,.1),0 1px 2px 0 rgba(0,0,0,.06)}.text-center{text-align:center}.text-gray-200{--text-opacity:1;color:#edf2f7;color:rgba(237,242,247,var(--text-opacity))}.text-gray-300{--text-opacity:1;color:#e2e8f0;color:rgba(226,232,240,var(--text-opacity))}.text-gray-400{--text-opacity:1;color:#cbd5e0;color:rgba(203,213,224,var(--text-opacity))}.text-gray-500{--text-opacity:1;color:#a0aec0;color:rgba(160,174,192,var(--text-opacity))}.text-gray-600{--text-opacity:1;color:#718096;color:rgba(113,128,150,var(--text-opacity))}.text-gray-700{--text-opacity:1;color:#4a5568;color:rgba(74,85,104,var(--text-opacity))}.text-gray-900{--text-opacity:1;color:#1a202c;color:rgba(26,32,44,var(--text-opacity))}.underline{text-decoration:underline}.antialiased{-webkit-font-smoothing:antialiased;-moz-osx-font-smoothing:grayscale}.w-5{width:1.25rem}.w-8{width:2rem}.w-auto{width:auto}.grid-cols-1{grid-template-columns:repeat(1,minmax(0,1fr))}@media (min-width:640px){.sm\:rounded-lg{border-radius:.5rem}.sm\:block{display:block}.sm\:items-center{align-items:center}.sm\:justify-start{justify-content:flex-start}.sm\:justify-between{justify-content:space-between}.sm\:h-20{height:5rem}.sm\:ml-0{margin-left:0}.sm\:px-6{padding-left:1.5rem;padding-right:1.5rem}.sm\:pt-0{padding-top:0}.sm\:text-left{text-align:left}.sm\:text-right{text-align:right}}@media (min-width:768px){.md\:border-t-0{border-top-width:0}.md\:border-l{border-left-width:1px}.md\:grid-cols-2{grid-template-columns:repeat(2,minmax(0,1fr))}}@media (min-width:1024px){.lg\:px-8{padding-left:2rem;padding-right:2rem}}@media (prefers-color-scheme:dark){.dark\:bg-gray-800{--bg-opacity:1;background-color:#2d3748;background-color:rgba(45,55,72,var(--bg-opacity))}.dark\:bg-gray-900{--bg-opacity:1;background-color:#1a202c;background-color:rgba(26,32,44,var(--bg-opacity))}.dark\:border-gray-700{--border-opacity:1;border-color:#4a5568;border-color:rgba(74,85,104,var(--border-opacity))}.dark\:text-white{--text-opacity:1;color:#fff;color:rgba(255,255,255,var(--text-opacity))}.dark\:text-gray-400{--text-opacity:1;color:#cbd5e0;color:rgba(203,213,224,var(--text-opacity))}}
        </style>

        <style>
            body {
                font-family: 'Nunito', sans-serif;
            }

            /* Contact Section Styles */
            .contact-wrapper {
                display: flex;
                flex-direction: column;
                background: linear-gradient(135deg, rgba(255,255,255,0.8) 0%, rgba(255,255,255,0.4) 100%);
                backdrop-filter: blur(20px);
                -webkit-backdrop-filter: blur(20px);
                border-radius: 24px;
                border: 1px solid rgba(255,255,255,0.5);
                box-shadow: 0 20px 40px rgba(0,0,0,0.08);
                overflow: hidden;
                margin-top: 3rem;
                margin-bottom: 3rem;
            }

            @media (min-width: 768px) {
                .contact-wrapper {
                    flex-direction: row;
                }
            }

            .contact-info {
                flex: 1;
                padding: 3rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
            }

            .contact-title {
                font-size: 2.5rem;
                font-weight: 800;
                margin-bottom: 1rem;
                line-height: 1.2;
                margin-top: 0;
            }

            .contact-subtitle {
                font-size: 1.1rem;
                opacity: 0.9;
                margin-bottom: 3rem;
                line-height: 1.6;
            }

            .contact-details {
                display: flex;
                flex-direction: column;
                gap: 2rem;
            }

            .contact-item {
                display: flex;
                align-items: center;
                gap: 1.5rem;
            }

            .contact-icon-wrapper {
                display: flex;
                align-items: center;
                justify-content: center;
                width: 50px;
                height: 50px;
                background: rgba(255,255,255,0.2);
                border-radius: 50%;
                backdrop-filter: blur(10px);
                transition: transform 0.3s ease, background 0.3s ease;
                flex-shrink: 0;
            }

            .contact-item:hover .contact-icon-wrapper {
                transform: scale(1.1);
                background: rgba(255,255,255,0.3);
            }

            .contact-item-title {
                font-size: 1.2rem;
                font-weight: 600;
                margin: 0 0 0.25rem 0;
            }

            .contact-item-text {
                margin: 0;
                opacity: 0.8;
                font-size: 1rem;
            }

            .contact-form-container {
                flex: 1.2;
                padding: 3rem;
                background: white;
                display: flex;
                flex-direction: column;
                justify-content: center;
            }

            .form-title {
                font-size: 2rem;
                font-weight: 700;
                color: #2d3748;
                margin-bottom: 2rem;
                margin-top: 0;
            }

            .form-group {
                margin-bottom: 1.5rem;
                text-align: left;
            }

            .form-label {
                display: block;
                font-size: 0.95rem;
                font-weight: 600;
                color: #4a5568;
                margin-bottom: 0.5rem;
            }

            .form-input {
                width: 100%;
                padding: 0.8rem 1.2rem;
                font-size: 1rem;
                font-family: inherit;
                border: 2px solid #e2e8f0;
                border-radius: 12px;
                background: #f7fafc;
                transition: all 0.3s ease;
                outline: none;
                box-sizing: border-box;
                color: #2d3748;
            }

            .form-input:focus {
                border-color: #667eea;
                background: white;
                box-shadow: 0 0 0 4px rgba(102, 126, 234, 0.1);
            }

            .submit-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
                gap: 0.75rem;
                background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                color: white;
                font-size: 1.1rem;
                font-weight: 600;
                padding: 1rem 2rem;
                border: none;
                border-radius: 12px;
                cursor: pointer;
                transition: all 0.3s ease;
                width: 100%;
                margin-top: 1rem;
                box-shadow: 0 10px 20px rgba(118, 75, 162, 0.2);
            }

            .submit-btn:hover {
                transform: translateY(-2px);
                box-shadow: 0 15px 25px rgba(118, 75, 162, 0.3);
            }

            .submit-btn:active {
                transform: translateY(0);
            }

            @media (prefers-color-scheme: dark) {
                .contact-form-container {
                    background: #1a202c;
                }
                .contact-wrapper {
                    background: rgba(30, 41, 59, 0.7);
                    border-color: rgba(255,255,255,0.1);
                }
                .form-title {
                    color: #f7fafc;
                }
                .form-label {
                    color: #a0aec0;
                }
                .form-input {
                    background: #2d3748;
                    border-color: #4a5568;
                    color: #e2e8f0;
                }
                .form-input:focus {
                    background: #1a202c;
                    border-color: #667eea;
                }
            }
        </style>
    </head>
    <body class="antialiased">
            @if (Route::has('login'))
                <header class="fixed top-0 left-0 right-0 px-6 py-4 flex justify-between items-center bg-white shadow-sm" style="z-index: 50; background: rgba(255,255,255,0.95); backdrop-filter: blur(10px); border-bottom: 1px solid #edf2f7; width: 100%;">
                    <div class="flex items-center" style="display: flex; align-items: center;">
                        <img src="{{asset('favicon_io/android-chrome-192x192.png')}}" height="32px" width="32px" class="mr-2" style="margin-right: 10px;">
                        <span style="font-weight: 800; font-size: 1.25rem; color: #8a6dd9;">Unifiedtransform</span>
                    </div>
                    <nav class="flex items-center flex-wrap" style="display: flex; align-items: center; gap: 20px;">
                        <a href="#features" class="text-gray-700 font-semibold" style="text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='#667eea'" onmouseout="this.style.color='#4a5568'">Features</a>
                        <a href="#how-it-works" class="text-gray-700 font-semibold" style="text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='#667eea'" onmouseout="this.style.color='#4a5568'">How it works</a>
                        <a href="#modules" class="text-gray-700 font-semibold" style="text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='#667eea'" onmouseout="this.style.color='#4a5568'">Modules</a>
                        <a href="#pricing" class="text-gray-700 font-semibold" style="text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='#667eea'" onmouseout="this.style.color='#4a5568'">Pricing</a>
                        <a href="#contact" class="text-gray-700 font-semibold" style="text-decoration: none; transition: color 0.3s;" onmouseover="this.style.color='#667eea'" onmouseout="this.style.color='#4a5568'">Contact Us</a>
                        @auth
                            <a href="{{ url('/home') }}" class="text-gray-700 font-semibold" style="text-decoration: none;">Home</a>
                        @else
                            <a href="{{ route('login') }}" class="text-white font-semibold" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 8px 20px; border-radius: 8px; text-decoration: none; box-shadow: 0 4px 6px rgba(102, 126, 234, 0.25); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">Login</a>
                        @endauth
                    </nav>
                </header>
            @endif

            <div class="max-w-6xl mx-auto sm:px-6 lg:px-8" style="padding-top: 100px;">
                <div class="flex justify-center pt-8 sm:justify-start sm:pt-0" style="align-items: center;">
                    <h1 class="text-gray-900 dark:text-white"><img src="{{asset('favicon_io/android-chrome-512x512.png')}}" class="mr-2" height="50px" width="50px"><span style="font-size: 45px; color: #8a6dd9;">nifiedtransform</span> - <small>Powered by &nbsp;</small></h1>
                    <svg viewBox="0 0 651 192" fill="none" xmlns="http://www.w3.org/2000/svg" class="h-16 w-auto text-gray-700 sm:h-20">
                        <g clip-path="url(#clip0)" fill="#EF3B2D">
                            <path d="M248.032 44.676h-16.466v100.23h47.394v-14.748h-30.928V44.676zM337.091 87.202c-2.101-3.341-5.083-5.965-8.949-7.875-3.865-1.909-7.756-2.864-11.669-2.864-5.062 0-9.69.931-13.89 2.792-4.201 1.861-7.804 4.417-10.811 7.661-3.007 3.246-5.347 6.993-7.016 11.239-1.672 4.249-2.506 8.713-2.506 13.389 0 4.774.834 9.26 2.506 13.459 1.669 4.202 4.009 7.925 7.016 11.169 3.007 3.246 6.609 5.799 10.811 7.66 4.199 1.861 8.828 2.792 13.89 2.792 3.913 0 7.804-.955 11.669-2.863 3.866-1.908 6.849-4.533 8.949-7.875v9.021h15.607V78.182h-15.607v9.02zm-1.431 32.503c-.955 2.578-2.291 4.821-4.009 6.73-1.719 1.91-3.795 3.437-6.229 4.582-2.435 1.146-5.133 1.718-8.091 1.718-2.96 0-5.633-.572-8.019-1.718-2.387-1.146-4.438-2.672-6.156-4.582-1.719-1.909-3.032-4.152-3.938-6.73-.909-2.577-1.36-5.298-1.36-8.161 0-2.864.451-5.585 1.36-8.162.905-2.577 2.219-4.819 3.938-6.729 1.718-1.908 3.77-3.437 6.156-4.582 2.386-1.146 5.059-1.718 8.019-1.718 2.958 0 5.656.572 8.091 1.718 2.434 1.146 4.51 2.674 6.229 4.582 1.718 1.91 3.054 4.152 4.009 6.729.953 2.577 1.432 5.298 1.432 8.162-.001 2.863-.479 5.584-1.432 8.161zM463.954 87.202c-2.101-3.341-5.083-5.965-8.949-7.875-3.865-1.909-7.756-2.864-11.669-2.864-5.062 0-9.69.931-13.89 2.792-4.201 1.861-7.804 4.417-10.811 7.661-3.007 3.246-5.347 6.993-7.016 11.239-1.672 4.249-2.506 8.713-2.506 13.389 0 4.774.834 9.26 2.506 13.459 1.669 4.202 4.009 7.925 7.016 11.169 3.007 3.246 6.609 5.799 10.811 7.66 4.199 1.861 8.828 2.792 13.89 2.792 3.913 0 7.804-.955 11.669-2.863 3.866-1.908 6.849-4.533 8.949-7.875v9.021h15.607V78.182h-15.607v9.02zm-1.432 32.503c-.955 2.578-2.291 4.821-4.009 6.73-1.719 1.91-3.795 3.437-6.229 4.582-2.435 1.146-5.133 1.718-8.091 1.718-2.96 0-5.633-.572-8.019-1.718-2.387-1.146-4.438-2.672-6.156-4.582-1.719-1.909-3.032-4.152-3.938-6.73-.909-2.577-1.36-5.298-1.36-8.161 0-2.864.451-5.585 1.36-8.162.905-2.577 2.219-4.819 3.938-6.729 1.718-1.908 3.77-3.437 6.156-4.582 2.386-1.146 5.059-1.718 8.019-1.718 2.958 0 5.656.572 8.091 1.718 2.434 1.146 4.51 2.674 6.229 4.582 1.718 1.91 3.054 4.152 4.009 6.729.953 2.577 1.432 5.298 1.432 8.162 0 2.863-.479 5.584-1.432 8.161zM650.772 44.676h-15.606v100.23h15.606V44.676zM365.013 144.906h15.607V93.538h26.776V78.182h-42.383v66.724zM542.133 78.182l-19.616 51.096-19.616-51.096h-15.808l25.617 66.724h19.614l25.617-66.724h-15.808zM591.98 76.466c-19.112 0-34.239 15.706-34.239 35.079 0 21.416 14.641 35.079 36.239 35.079 12.088 0 19.806-4.622 29.234-14.688l-10.544-8.158c-.006.008-7.958 10.449-19.832 10.449-13.802 0-19.612-11.127-19.612-16.884h51.777c2.72-22.043-11.772-40.877-33.023-40.877zm-18.713 29.28c.12-1.284 1.917-16.884 18.589-16.884 16.671 0 18.697 15.598 18.813 16.884h-37.402zM184.068 43.892c-.024-.088-.073-.165-.104-.25-.058-.157-.108-.316-.191-.46-.056-.097-.137-.176-.203-.265-.087-.117-.161-.242-.265-.345-.085-.086-.194-.148-.29-.223-.109-.085-.206-.182-.327-.252l-.002-.001-.002-.002-35.648-20.524a2.971 2.971 0 00-2.964 0l-35.647 20.522-.002.002-.002.001c-.121.07-.219.167-.327.252-.096.075-.205.138-.29.223-.103.103-.178.228-.265.345-.066.089-.147.169-.203.265-.083.144-.133.304-.191.46-.031.085-.08.162-.104.25-.067.249-.103.51-.103.776v38.979l-29.706 17.103V24.493a3 3 0 00-.103-.776c-.024-.088-.073-.165-.104-.25-.058-.157-.108-.316-.191-.46-.056-.097-.137-.176-.203-.265-.087-.117-.161-.242-.265-.345-.085-.086-.194-.148-.29-.223-.109-.085-.206-.182-.327-.252l-.002-.001-.002-.002L40.098 1.396a2.971 2.971 0 00-2.964 0L1.487 21.919l-.002.002-.002.001c-.121.07-.219.167-.327.252-.096.075-.205.138-.29.223-.103.103-.178.228-.265.345-.066.089-.147.169-.203.265-.083.144-.133.304-.191.46-.031.085-.08.162-.104.25-.067.249-.103.51-.103.776v122.09c0 1.063.568 2.044 1.489 2.575l71.293 41.045c.156.089.324.143.49.202.078.028.15.074.23.095a2.98 2.98 0 001.524 0c.069-.018.132-.059.2-.083.176-.061.354-.119.519-.214l71.293-41.045a2.971 2.971 0 001.489-2.575v-38.979l34.158-19.666a2.971 2.971 0 001.489-2.575V44.666a3.075 3.075 0 00-.106-.774zM74.255 143.167l-29.648-16.779 31.136-17.926.001-.001 34.164-19.669 29.674 17.084-21.772 12.428-43.555 24.863zm68.329-76.259v33.841l-12.475-7.182-17.231-9.92V49.806l12.475 7.182 17.231 9.92zm2.97-39.335l29.693 17.095-29.693 17.095-29.693-17.095 29.693-17.095zM54.06 114.089l-12.475 7.182V46.733l17.231-9.92 12.475-7.182v74.537l-17.231 9.921zM38.614 7.398l29.693 17.095-29.693 17.095L8.921 24.493 38.614 7.398zM5.938 29.632l12.475 7.182 17.231 9.92v79.676l.001.005-.001.006c0 .114.032.221.045.333.017.146.021.294.059.434l.002.007c.032.117.094.222.14.334.051.124.088.255.156.371a.036.036 0 00.004.009c.061.105.149.191.222.288.081.105.149.22.244.314l.008.01c.084.083.19.142.284.215.106.083.202.178.32.247l.013.005.011.008 34.139 19.321v34.175L5.939 144.867V29.632h-.001zm136.646 115.235l-65.352 37.625V148.31l48.399-27.628 16.953-9.677v33.862zm35.646-61.22l-29.706 17.102V66.908l17.231-9.92 12.475-7.182v33.841z"/>
                        </g>
                    </svg>
                    <span class="ml-4" style="font-size: 50px; color:#1a202c;">+</span>
                    <img class="ml-4" src="{{asset('bootstrap-5-1.svg')}}" height="70" alt="Bootstrap 5">
                </div>

                <!-- Apps Showcase Section -->
                <div class="mt-16 bg-white dark:bg-gray-900 overflow-hidden sm:rounded-2xl shadow-xl">
                    <div class="p-8 border-b border-gray-200 dark:border-gray-800 text-center">
                        <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-2">Our Unified Ecosystem</h2>
                        <p class="text-gray-600 dark:text-gray-400">Experience seamless school management across all your devices.</p>
                    </div>

                    <!-- 1. Teacher App (Left Features, Right Image) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-10 lg:p-16 border-b border-gray-200 dark:border-gray-800 items-center">
                        <div class="order-2 md:order-1">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Teacher App</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">Empower your educators with tools designed to streamline daily tasks, so they can focus on what matters most—teaching.</p>
                            
                            <ul class="space-y-4">
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-indigo-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-200">Real-Time Gradebook</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Input and sync student scores instantly from the classroom.</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-indigo-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-200">Smart Attendance</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Mark attendance with one tap, saving valuable instruction time.</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-indigo-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-200">Lesson Planner</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Build and manage your daily curriculum seamlessly.</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="order-1 md:order-2 flex justify-center">
                            <img src="/imgs/screenshots/teacher_app.webp" alt="Teacher App Screenshot" class="rounded-2xl shadow-2xl hover:shadow-indigo-500/20 transition-shadow duration-300 transform hover:-translate-y-2" style="max-height: 400px; width: auto; object-fit: contain;">
                        </div>
                    </div>

                    <!-- 2. Parent App (Left Image, Right Features) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-10 lg:p-16 border-b border-gray-200 dark:border-gray-800 items-center bg-gray-50 dark:bg-gray-800">
                        <div class="order-1 flex justify-center">
                            <img src="/imgs/screenshots/parent_app.webp" alt="Parent App Screenshot" class="rounded-2xl shadow-2xl hover:shadow-green-500/20 transition-shadow duration-300 transform hover:-translate-y-2" style="max-height: 400px; width: auto; object-fit: contain;">
                        </div>
                        <div class="order-2">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Parent App</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">Bridge the gap between home and school. Stay intimately involved in your child's academic journey with real-time updates.</p>
                            
                            <ul class="space-y-4">
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-green-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-200">Live Bus Tracking</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Never miss the bus with real-time GPS location updates.</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-green-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-200">Academic Progress</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Track grades, assignments, and exam results on the go.</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-green-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-200">Instant Notifications</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Receive immediate alerts for school announcements and attendance.</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- 3. Student App (Left Features, Right Image) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-10 lg:p-16 border-b border-gray-200 dark:border-gray-800 items-center">
                        <div class="order-2 md:order-1">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Student App</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">A digital companion for every student. Stay organized, submit homework, and access study materials instantly.</p>
                            
                            <ul class="space-y-4">
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-blue-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"></path></svg>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-200">Homework & Assignments</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">View daily tasks and upload submissions right from your phone.</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-blue-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-200">E-Library Access</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Read study notes, presentations, and borrow books digitally.</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-blue-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-200">Interactive Timetable</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Keep track of upcoming classes and schedule changes.</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                        <div class="order-1 md:order-2 flex justify-center">
                            <img src="/imgs/screenshots/student_app.webp" alt="Student App Screenshot" class="rounded-2xl shadow-2xl hover:shadow-blue-500/20 transition-shadow duration-300 transform hover:-translate-y-2" style="max-height: 400px; width: auto; object-fit: contain;">
                        </div>
                    </div>

                    <!-- 4. Driver App (Left Image, Right Features) -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 p-10 lg:p-16 border-b border-gray-200 dark:border-gray-800 items-center bg-gray-50 dark:bg-gray-800">
                        <div class="order-1 flex justify-center">
                            <img src="/imgs/screenshots/driver_app.webp" alt="Driver App Screenshot" class="rounded-2xl shadow-2xl hover:shadow-orange-500/20 transition-shadow duration-300 transform hover:-translate-y-2" style="max-height: 400px; width: auto; object-fit: contain;">
                        </div>
                        <div class="order-2">
                            <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Driver App</h3>
                            <p class="text-gray-600 dark:text-gray-400 mb-6">Ensure student safety and fleet efficiency. Equip your drivers with precise routing and passenger tracking.</p>
                            
                            <ul class="space-y-4">
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-orange-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"></path></svg>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-200">Optimized Routing</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Get turn-by-turn navigation for the most efficient daily routes.</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-orange-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-200">Roster Management</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">Access real-time passenger lists for each specific trip.</p>
                                    </div>
                                </li>
                                <li class="flex items-start">
                                    <svg class="h-6 w-6 text-orange-500 mr-3 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                                    <div>
                                        <h4 class="text-lg font-semibold text-gray-900 dark:text-gray-200">Emergency Alerts</h4>
                                        <p class="text-sm text-gray-500 dark:text-gray-400">One-tap panic buttons for instant school notification.</p>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- CSS FOR NEW SECTIONS -->
                <style>
                /* New Sections CSS */
                .section-container {
                    padding: 5rem 0;
                    border-bottom: 1px solid rgba(0,0,0,0.05);
                }
                .section-title {
                    font-size: 2.5rem;
                    font-weight: 800;
                    text-align: center;
                    color: #1a202c;
                    margin-bottom: 1rem;
                }
                .section-subtitle {
                    font-size: 1.1rem;
                    text-align: center;
                    color: #718096;
                    margin-bottom: 4rem;
                    max-width: 600px;
                    margin-left: auto;
                    margin-right: auto;
                }
                
                /* Features Grid */
                .features-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 2rem;
                }
                .feature-card {
                    background: white;
                    padding: 2.5rem;
                    border-radius: 16px;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.05);
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                    text-align: center;
                }
                .feature-card:hover {
                    transform: translateY(-5px);
                    box-shadow: 0 20px 40px rgba(0,0,0,0.08);
                }
                .feature-icon {
                    width: 64px;
                    height: 64px;
                    background: rgba(102, 126, 234, 0.1);
                    color: #667eea;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 1.5rem auto;
                }
                .feature-title {
                    font-size: 1.25rem;
                    font-weight: 700;
                    margin-bottom: 1rem;
                    color: #2d3748;
                }
                .feature-desc {
                    color: #718096;
                    line-height: 1.6;
                }
                
                /* How it Works */
                .steps-container {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
                    gap: 3rem;
                    position: relative;
                }
                .step-card {
                    text-align: center;
                    position: relative;
                }
                .step-number {
                    width: 50px;
                    height: 50px;
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                    font-size: 1.5rem;
                    font-weight: bold;
                    border-radius: 50%;
                    display: flex;
                    align-items: center;
                    justify-content: center;
                    margin: 0 auto 1.5rem auto;
                    box-shadow: 0 10px 20px rgba(118, 75, 162, 0.3);
                }
                
                /* Modules */
                .modules-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 1.5rem;
                }
                .module-item {
                    display: flex;
                    align-items: center;
                    gap: 1.5rem;
                    background: white;
                    padding: 1.5rem;
                    border-radius: 12px;
                    border: 1px solid #edf2f7;
                    transition: all 0.3s;
                }
                .module-item:hover {
                    border-color: #667eea;
                    box-shadow: 0 10px 20px rgba(102, 126, 234, 0.1);
                }
                
                /* Pricing */
                .pricing-grid {
                    display: grid;
                    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
                    gap: 2rem;
                }
                .pricing-card {
                    background: white;
                    border-radius: 20px;
                    padding: 3rem 2rem;
                    text-align: center;
                    border: 1px solid #edf2f7;
                    transition: transform 0.3s;
                    position: relative;
                    overflow: hidden;
                }
                .pricing-card:hover {
                    transform: translateY(-10px);
                    box-shadow: 0 30px 60px rgba(0,0,0,0.1);
                }
                .pricing-card.popular {
                    border-color: #667eea;
                    box-shadow: 0 20px 40px rgba(102, 126, 234, 0.15);
                }
                .popular-badge {
                    position: absolute;
                    top: 20px;
                    right: -30px;
                    background: #667eea;
                    color: white;
                    padding: 5px 40px;
                    transform: rotate(45deg);
                    font-size: 0.8rem;
                    font-weight: bold;
                }
                .price {
                    font-size: 3rem;
                    font-weight: 800;
                    color: #1a202c;
                    margin: 1.5rem 0;
                }
                .price span {
                    font-size: 1.2rem;
                    color: #a0aec0;
                }
                .pricing-features {
                    list-style: none;
                    padding: 0;
                    margin: 2rem 0;
                    text-align: left;
                }
                .pricing-features li {
                    padding: 0.5rem 0;
                    color: #4a5568;
                    display: flex;
                    align-items: center;
                    gap: 10px;
                }
                .pricing-btn {
                    display: block;
                    width: 100%;
                    padding: 1rem;
                    border-radius: 10px;
                    background: #edf2f7;
                    color: #4a5568;
                    font-weight: bold;
                    text-decoration: none;
                    transition: all 0.3s;
                }
                .pricing-card.popular .pricing-btn {
                    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
                    color: white;
                }
                .pricing-btn:hover {
                    opacity: 0.9;
                }
                </style>
                
                <!-- FEATURES SECTION -->
                <div id="features" class="section-container">
                    <h2 class="section-title">Powerful Features</h2>
                    <p class="section-subtitle">Everything you need to manage your institution effectively and efficiently in one place.</p>
                    <div class="features-grid">
                        <div class="feature-card">
                            <div class="feature-icon">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                            </div>
                            <h3 class="feature-title">Top-tier Security</h3>
                            <p class="feature-desc">Your school's data is protected with enterprise-grade encryption and automated backups.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            </div>
                            <h3 class="feature-title">Lightning Fast</h3>
                            <p class="feature-desc">Optimized performance ensuring quick load times for attendance, grades, and reports.</p>
                        </div>
                        <div class="feature-card">
                            <div class="feature-icon">
                                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 15a4 4 0 004 4h9a5 5 0 10-.1-9.999 5.002 5.002 0 10-9.78 2.096A4.001 4.001 0 003 15z"></path></svg>
                            </div>
                            <h3 class="feature-title">Cloud Native</h3>
                            <p class="feature-desc">Access your dashboard from anywhere, anytime, on any device with our cloud infrastructure.</p>
                        </div>
                    </div>
                </div>

                <!-- HOW IT WORKS SECTION -->
                <div id="how-it-works" class="section-container">
                    <h2 class="section-title">How It Works</h2>
                    <p class="section-subtitle">Get your school up and running on our platform in three simple steps.</p>
                    <div class="steps-container">
                        <div class="step-card">
                            <div class="step-number">1</div>
                            <h3 class="feature-title">Onboarding</h3>
                            <p class="feature-desc">We import your existing student and staff data seamlessly into the new system.</p>
                        </div>
                        <div class="step-card">
                            <div class="step-number">2</div>
                            <h3 class="feature-title">Configuration</h3>
                            <p class="feature-desc">Customize grading systems, attendance rules, and communication templates.</p>
                        </div>
                        <div class="step-card">
                            <div class="step-number">3</div>
                            <h3 class="feature-title">Go Live</h3>
                            <p class="feature-desc">Train your staff and launch the portal for parents, students, and teachers.</p>
                        </div>
                    </div>
                </div>

                <!-- MODULES SECTION -->
                <div id="modules" class="section-container">
                    <h2 class="section-title">Comprehensive Modules</h2>
                    <p class="section-subtitle">A modular design means you only use what you need, but have access to everything.</p>
                    <div class="modules-grid">
                        <div class="module-item">
                            <div class="feature-icon" style="margin:0; width:48px; height:48px;">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <div>
                                <h4 style="margin:0; font-weight:700; color:#2d3748;">Student Management</h4>
                                <p style="margin:5px 0 0 0; font-size:0.9rem; color:#718096;">Enrollments, profiles, and history.</p>
                            </div>
                        </div>
                        <div class="module-item">
                            <div class="feature-icon" style="margin:0; width:48px; height:48px;">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <h4 style="margin:0; font-weight:700; color:#2d3748;">Finance & Fees</h4>
                                <p style="margin:5px 0 0 0; font-size:0.9rem; color:#718096;">Invoicing, online payments, and payroll.</p>
                            </div>
                        </div>
                        <div class="module-item">
                            <div class="feature-icon" style="margin:0; width:48px; height:48px;">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            </div>
                            <div>
                                <h4 style="margin:0; font-weight:700; color:#2d3748;">Academics & Exams</h4>
                                <p style="margin:5px 0 0 0; font-size:0.9rem; color:#718096;">Timetables, gradebooks, and report cards.</p>
                            </div>
                        </div>
                        <div class="module-item">
                            <div class="feature-icon" style="margin:0; width:48px; height:48px;">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"></path></svg>
                            </div>
                            <div>
                                <h4 style="margin:0; font-weight:700; color:#2d3748;">Communication</h4>
                                <p style="margin:5px 0 0 0; font-size:0.9rem; color:#718096;">SMS, Email, and Push Notifications.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- PRICING SECTION -->
                <div id="pricing" class="section-container">
                    <h2 class="section-title">Simple, Transparent Pricing</h2>
                    <p class="section-subtitle">No hidden fees. Choose the plan that fits your institution's size and needs.</p>
                    <div class="pricing-grid">
                        <div class="pricing-card">
                            <h3 style="color:#2d3748; margin-top:0;">Starter</h3>
                            <div class="price">$49<span>/mo</span></div>
                            <p style="color:#718096;">Perfect for small schools and kindergartens.</p>
                            <ul class="pricing-features">
                                <li><svg class="w-5 h-5" style="color:#48bb78" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Up to 500 Students</li>
                                <li><svg class="w-5 h-5" style="color:#48bb78" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Basic Modules</li>
                                <li><svg class="w-5 h-5" style="color:#48bb78" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Email Support</li>
                            </ul>
                            <a href="#contact" class="pricing-btn">Get Started</a>
                        </div>
                        <div class="pricing-card popular">
                            <div class="popular-badge">POPULAR</div>
                            <h3 style="color:#2d3748; margin-top:0;">Professional</h3>
                            <div class="price">$99<span>/mo</span></div>
                            <p style="color:#718096;">Ideal for growing schools requiring more power.</p>
                            <ul class="pricing-features">
                                <li><svg class="w-5 h-5" style="color:#48bb78" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Up to 2,000 Students</li>
                                <li><svg class="w-5 h-5" style="color:#48bb78" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> All Modules Included</li>
                                <li><svg class="w-5 h-5" style="color:#48bb78" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Priority Support</li>
                                <li><svg class="w-5 h-5" style="color:#48bb78" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Custom Domain</li>
                            </ul>
                            <a href="#contact" class="pricing-btn">Get Started</a>
                        </div>
                        <div class="pricing-card">
                            <h3 style="color:#2d3748; margin-top:0;">Enterprise</h3>
                            <div class="price">Custom</div>
                            <p style="color:#718096;">For large institutions and university campuses.</p>
                            <ul class="pricing-features">
                                <li><svg class="w-5 h-5" style="color:#48bb78" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Unlimited Students</li>
                                <li><svg class="w-5 h-5" style="color:#48bb78" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Dedicated Server</li>
                                <li><svg class="w-5 h-5" style="color:#48bb78" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> 24/7 Phone Support</li>
                                <li><svg class="w-5 h-5" style="color:#48bb78" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> API Access</li>
                            </ul>
                            <a href="#contact" class="pricing-btn">Contact Sales</a>
                        </div>
                    </div>
                </div>

                <!-- Contact Us Section -->
                <div id="contact" class="contact-wrapper">
                    <div class="contact-info sm:text-left">
                        <h2 class="contact-title">Get in Touch</h2>
                        <p class="contact-subtitle">Have questions or need assistance? We're here to help. Reach out to our dedicated support team today!</p>
                        
                        <div class="contact-details">
                            <div class="contact-item">
                                <div class="contact-icon-wrapper">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="contact-item-title">Our Office</h4>
                                    <p class="contact-item-text">123 Education Lane, Learning City, 45678</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon-wrapper">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="contact-item-title">Email Us</h4>
                                    <p class="contact-item-text">support@unifiedtransform.com</p>
                                </div>
                            </div>
                            <div class="contact-item">
                                <div class="contact-icon-wrapper">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                </div>
                                <div>
                                    <h4 class="contact-item-title">Call Us</h4>
                                    <p class="contact-item-text">+1 (555) 123-4567</p>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="contact-form-container">
                        <form class="contact-form" action="{{ route('saas.contact') }}" method="POST">
                            @csrf
                            <h3 class="form-title">Send us a Message</h3>
                            <div class="form-group">
                                <label for="name" class="form-label">Full Name</label>
                                <input type="text" id="name" name="name" class="form-input" placeholder="John Doe" required>
                            </div>
                            <div class="form-group">
                                <label for="email" class="form-label">Email Address</label>
                                <input type="email" id="email" name="email" class="form-input" placeholder="john@example.com" required>
                            </div>
                            <input type="hidden" name="subject" value="Website General Inquiry">
                            <div class="form-group">
                                <label for="message" class="form-label">Message</label>
                                <textarea id="message" name="message" rows="4" class="form-input" placeholder="How can we help you?" required></textarea>
                            </div>
                            <button type="submit" class="submit-btn">
                                <span>Send Message</span>
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" style="width: 1.25rem; height: 1.25rem;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>

                <div class="flex justify-center mt-4 sm:items-center sm:justify-between">
                    {{-- <div class="text-center text-sm text-gray-500 sm:text-left">
                        <div class="flex items-center">
                            <svg fill="none" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" stroke="currentColor" class="-mt-px w-5 h-5 text-gray-400">
                                <path d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>

                            <a href="https://laravel.bigcartel.com" class="ml-1 underline">
                                Shop
                            </a>

                            <svg fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" viewBox="0 0 24 24" class="ml-4 -mt-px w-5 h-5 text-gray-400">
                                <path d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path>
                            </svg>

                            <a href="https://github.com/sponsors/taylorotwell" class="ml-1 underline">
                                Sponsor
                            </a>
                        </div>
                    </div> --}}

                    <div class="ml-4 text-center text-sm text-gray-500 sm:text-right sm:ml-0">
                        Laravel v{{ Illuminate\Foundation\Application::VERSION }} (PHP v{{ PHP_VERSION }}) . Bootstrap 5.x
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>
