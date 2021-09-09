<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-100 leading-tight">
                {{ __('Editing Feedback No. '.$feedback->id) }}
            </h2>
        </div>
    </x-slot>

    <div class="py-6">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-gray-100 dark:bg-gray-800">
                    <form method="POST" action="{{ route('feedback.update', $feedback) }}">
                        @method("PUT")
                        @csrf
                        <div class="mb-4">
                            <x-label for="description" :value="__('Feedback Description')" />            
                            <textarea 
                                class="block mt-1 w-full form-control @error('description') border-red-500 dark:border-red-300 @enderror" 
                                id="description" name="description" placeholder="Feedback Description">{{$feedback->description}}</textarea>
                            @error('description')
                                <div class="text-red-400 text-xs">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-4">
                            <x-label for="rating">
                                Feedback Rating
                            </x-label>
                            <select class="block mt-1 w-full form-control"
                                id="rating" name="rating">
                                @for ($i = 1; $i < 6; $i++)
                                    <option {{ $feedback->rating == $i ? "selected":"" }} value={{$i}}>{{$i}}</option>
                                @endfor
                            </select>
                            @error('rating')
                                <div class="text-red-400 text-xs">{{ $message }}</div>
                            @enderror
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
