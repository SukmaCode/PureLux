<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create an Account</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class=" min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-4xl bg-black rounded-2xl shadow-2xl flex">
        <!-- Left Side - Hero Section -->
        <div class="w-1/2 bg-gradient-to-br hidden md:flex from-yellow-600 via-yellow-700 to-yellow-900 p-12 flex-col justify-between relative overflow-hidden">
            <!-- Logo -->
            <div class="z-10">
                <h1 class="text-white text-xl font-bold">AMU</h1>
            </div>
            
            <!-- Decorative Mountain Image Placeholder -->
            <div class="absolute inset-0 opacity-40">
                <div class="absolute bottom-0 left-0 right-0 h-2/3 bg-gradient-to-t from-black/60 to-transparent"></div>
                <div class="absolute bottom-0 left-0 w-full h-1/2 bg-gradient-to-br from-yellow-900/40 to-black/40" style="clip-path: polygon(0 60%, 30% 40%, 60% 50%, 100% 30%, 100% 100%, 0 100%)"></div>
            </div>
            
            <!-- Text Content -->
            <div class="z-10">
                <h2 class="text-white text-xl font-bold leading-tight">
                    Capturing Moments<br>
                    Creating Memories
                </h2>
            </div>
            
            <!-- Back to Website Link -->
            <div class="z-10">
                <a href="#" class="text-white text-sm hover:text-yellow-200 transition-colors inline-flex items-center">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path>
                    </svg>
                    Back to website
                </a>
            </div>
        </div>

        <!-- Right Side - Form Section -->
        <div class="w-full bg-white p-12">
            <div class="max-w-md mx-auto">
                <h2 class="text-xl font-bold text-black mb-2">Create an account</h2>
                <p class="text-gray-600 text-sm mb-8">
                    Already have an account? 
                    <a href="#" class="text-yellow-600 hover:text-yellow-700 font-semibold">Log in</a>
                </p>

                <form>
                    <!-- Name Field -->
                     <div class="flex justify-between items-center gap-4">
                            <input 
                                type="text"
                                id="username" required
                                placeholder="Name"
                                value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>"
                                class="w-full px-2 py-2 bg-gray-100 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-600 focus:border-transparent text-black placeholder-gray-500"
                            >
                        </div>

                        <!-- Username Field -->
                        <div class="mb-4 w-full">
                            <input 
                                type="text" 
                                placeholder="Username" 
                                class="w-full px-2 py-2 bg-gray-100 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-600 focus:border-transparent text-black placeholder-gray-500"
                            >
                        </div>
                    </div>

                    <!-- Email Field -->
                    <div class="mb-4">
                        <input 
                            type="email" 
                            placeholder="Email" 
                            class="w-full px-2 py-2 bg-gray-100 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-600 focus:border-transparent text-black placeholder-gray-500"
                        >
                    </div>

                    <!-- Password Field -->
                    <div class="mb-6">
                        <input 
                            type="password" 
                            placeholder="Password" 
                            class="w-full px-2 py-2 bg-gray-100 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-yellow-600 focus:border-transparent text-black placeholder-gray-500"
                        >
                    </div>

                    <!-- Terms Checkbox -->
                    <div class="mb-6 flex items-start">
                        <input 
                            type="checkbox" 
                            id="terms" 
                            class="mt-1 w-4 h-4 text-yellow-600 border-gray-300 rounded focus:ring-yellow-500"
                        >
                        <label for="terms" class="ml-3 text-sm text-gray-600">
                            I agree to the <a href="#" class="text-yellow-600 hover:text-yellow-700">Terms & Conditions</a>
                        </label>
                    </div>

                    <!-- Create Account Button -->
                    <button 
                        type="submit" 
                        class="w-full bg-yellow-600 hover:bg-yellow-700 text-white font-semibold py-3 rounded-lg transition-colors mb-4"
                    >
                        Create account
                    </button>

                    <!-- Divider -->
                    <div class="relative my-6">
                        <div class="absolute inset-0 flex items-center">
                            <div class="w-full border-t border-gray-300"></div>
                        </div>
                        <div class="relative flex justify-center text-sm">
                            <span class="px-4 bg-white text-gray-500">Or continue with</span>
                        </div>
                    </div>

                    <!-- Social Login Buttons -->
                    <div class="grid grid-cols-2 gap-4">
                        <button 
                            type="button" 
                            class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24">
                                <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                                <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                                <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                                <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                            </svg>
                            <span class="text-black font-medium">Google</span>
                        </button>
                        
                        <button 
                            type="button" 
                            class="flex items-center justify-center px-4 py-3 border border-gray-300 rounded-lg hover:bg-gray-50 transition-colors"
                        >
                            <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12.001 2.002c-5.522 0-9.999 4.477-9.999 9.999 0 4.99 3.656 9.126 8.437 9.879v-6.988h-2.54v-2.891h2.54V9.798c0-2.508 1.493-3.891 3.776-3.891 1.094 0 2.24.195 2.24.195v2.459h-1.264c-1.24 0-1.628.772-1.628 1.563v1.875h2.771l-.443 2.891h-2.328v6.988C18.344 21.129 22 16.992 22 12.001c0-5.522-4.477-9.999-9.999-9.999z"/>
                            </svg>
                            <span class="text-black font-medium">Apple</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</body>
</html>