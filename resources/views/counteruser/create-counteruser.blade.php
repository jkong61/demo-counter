<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Creating Counter User') }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-gray-100 dark:bg-gray-800">
                    <form method="POST" action="{{ route('counteruser.store') }}">
                        @csrf

                        {{-- Flash Messages --}}

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div class="mb-4">
                                <x-label for="counteruseridentifier" :value="__('ID')" />
                                <input type="text"
                                    class="block mt-1 w-full form-control @error('counteruseridentifier') border-red-500 dark:border-red-300 @enderror" 
                                    id="counteruseridentifier" name="counteruseridentifier" placeholder="Counter User ID" value="{{ old('counteruseridentifier') }}"/>
                                @error('counteruseridentifier')
                                    <div class="text-red-400 text-xs">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <x-label for="counterusername" :value="__('Full Name')" />
                                <input type="text"
                                    class="block mt-1 w-full form-control @error('counterusername') border-red-500 dark:border-red-300 @enderror" 
                                    id="counterusername" name="counterusername" placeholder="Counter User Full Name" value="{{ old('counterusername') }}"/>
                                @error('counterusername')
                                    <div class="text-red-400 text-xs">{{ $message }}</div>
                                @enderror
                            </div>
    
                            <div class="mb-4">
                                <x-label for="counteruserdisplay" :value="__('Display Name')" />
                                <input type="text"
                                    class="block mt-1 w-full form-control @error('counteruserdisplay') border-red-500 dark:border-red-300 @enderror" 
                                    id="counteruserdisplay" name="counteruserdisplay" placeholder="Counter User Display Name" value="{{ old('counteruserdisplay') }}"/>
                                @error('counteruserdisplay')
                                    <div class="text-red-400 text-xs">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-4">
                                <x-label for="counteruserposition" :value="__('Position')" />
                                <input type="text"
                                    class="block mt-1 w-full form-control @error('counteruserposition') border-red-500 dark:border-red-300 @enderror" 
                                    id="counteruserposition" name="counteruserposition" placeholder="Counter User Position" value="{{ old('counteruserposition') }}"/>
                                @error('counteruserposition')
                                    <div class="text-red-400 text-xs">{{ $message }}</div>
                                @enderror
                            </div>   
                        </div>

                        <div class="space-x-2">
                            <button class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline focus:ring" type="submit">
                                Confirm
                            </button>
                            <x-back-button>Go Back</x-back-button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
