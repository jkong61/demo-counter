@php
    $string_limit = 40;
@endphp

<x-dashboard-layout>
    <x-slot name="title">
        {{__('feedback.dashboard_title')}}
    </x-slot>

    <x-filter-form action="{{ route('feedback.index') }}">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 min-w-full">
            <div class="mb-4">
                <x-label for="rating">
                    Feedback Rating
                </x-label>
                <select class="block mt-1 w-full form-control"
                    id="rating" name="rating">
                    <option value="">Select rating</option>
                    @for ($i = 1; $i < 6; $i++)
                        <option {{ request()->get('rating') == $i ? "selected":"" }} value={{$i}}>{{$i}}</option>
                    @endfor
                </select>
                @error('rating')
                    <div class="text-red-400 text-xs">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <x-label for="feedback_date_start">
                    Feedback Date Start
                </x-label>
                <input type="date" class="block mt-1 w-full form-control" value="{{ request()->get('feedback_date_start') }}"
                    id="feedback_date_start" name="feedback_date_start">
                @error('feedback_date_start')
                    <div class="text-red-400 text-xs">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <x-label for="feedback_date_end">
                    Feedback Date End
                </x-label>
                <input type="date" class="block mt-1 w-full form-control" value="{{ request()->get('feedback_date_end') }}"
                    id="feedback_date_end" name="feedback_date_end">
                @error('feedback_date_end')
                    <div class="text-red-400 text-xs">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </x-filter-form>

    @if (count($feedbacks) <= 0)
        <span class="text-gray-900 dark:text-gray-100">No feedbacks recorded.</span>
    @else
    <x-modal-container>
        <x-slot name="content">
            <input x-ref="token" type="hidden" value="{{ Cookie::get('api_token') }}"/>
            <x-table>
                <x-slot name="thead">
                    <tr>
                        <x-table-header-col>
                            No.
                        </x-table-header-col>
                        <x-table-header-col>
                            Description
                        </x-table-header-col>
                        <x-table-header-col>
                            Others
                        </x-table-header-col>
                        <x-table-header-col>
                            Date & Time
                        </x-table-header-col>
                        <x-table-header-col>
                            Rating
                        </x-table-header-col>
                        <x-table-header-col class="relative px-6 py-3">
                            <span class="sr-only">Actions</span>
                        </x-table-header-col>
                    </tr>

                </x-slot>

                <x-slot name="tbody">
                    @foreach ($feedbacks as $feedback)
                    <tr>
                        <x-table-body-col class="text-xs">
                            {{$feedback->id}}
                        </x-table-body-col>
                        <x-table-body-col>
                            <div class="text-sm font-semibold text-gray-900">
                                {{ !isset($feedback->description) 
                                ? "Not Provided" 
                                : (strlen($feedback->description) < $string_limit
                                ? $feedback->description 
                                : mb_strimwidth($feedback->description, 0, $string_limit)."...") }}
                            </div>
                        </x-table-body-col>
                        <x-table-body-col>
                            <div class="text-sm text-gray-900">Counter: 
                                @if(empty($feedback->loginsession))
                                    {{ $feedback->counter->number }} ({{ $feedback->counter->branch->name }})
                                @else
                                    {{$feedback->loginsession->counter->number}} ({{$feedback->loginsession->counter->branch->name}})
                                @endif
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-700">Cashier: 
                                @if(empty($feedback->loginsession))
                                    N/A
                                @elseif(empty($feedback->loginsession->counter_user))
                                    N/A
                                @else
                                    {{$feedback->loginsession->counter_user->name}}
                                @endif
                            </div>
                        </x-table-body-col>
                        <x-table-body-col>
                            <div class="text-sm text-gray-900">{{ date('d-m-Y H:i:s', $feedback->feedback_time_submission) }}</div>
                        </x-table-body-col>
                        <x-table-body-col>
                            <x-rating rating="{{$feedback->rating}}"></x-rating>
                        </x-table-body-col>
                        <x-table-body-col class="text-left space-x-2">
                            <a @click="openModal({{ $feedback->id }})" class="cursor-pointer text-sm font-medium text-indigo-600 hover:text-indigo-900">View</a>
                            <a href="{{ route('feedback.edit', $feedback) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">Edit</a>
                        </x-table-body-col>
                    </tr>
                    @endforeach
                </x-slot>

                <x-slot name="pagination">
                    {{ $feedbacks->appends(request()->query())->links() }}
                </x-slot>
            </x-table>
        </x-slot>

        <x-slot name="modalcontent">
            <div class="bg-white dark:bg-gray-600 px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                <div class="mt-3 text-center sm:mt-0 sm:mx-4 sm:text-left divide-y divide-gray-400 dark:divide-gray-100">
                    <div class="flex justify-between items-center mb-2">
                        <h3 class="text-xl leading-6 font-medium dark:text-gray-200 text-gray-900" id="modal-title">
                            Feedback No. <span x-html="contentData.id"></span>
                        </h3>
                        <button class="focus:ring focus:ring-indigo-500 dark:focus:ring-gray-100 rounded-md dark:text-gray-100">
                            <svg @click="closeModal()" xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 cursor-pointer" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd" />
                            </svg>
                        </button>
                    </div>
                    <div class="mb-2">
                        <x-modal-row title="Description" html="contentData.description"/>
                        <x-modal-row title="Rating" html="`${contentData.rating} / 5`"/>
                    </div>
                    <div class="mb-2">
                        <x-modal-row title="Employee Number" html="contentData.loginsession?.counter_user?.number ?? 'N/A'"/>
                        <x-modal-row title="Name" html="contentData.loginsession?.counter_user?.name ?? 'N/A'"/>
                    </div>
                    <div class="mb-2">
                        <x-modal-row title="Counter Number" html="contentData.loginsession?.counter?.number ?? contentData.counter?.number ?? 'N/A'"/>
                        <x-modal-row title="Branch" html="contentData.loginsession?.counter?.branch.name ?? contentData.counter?.branch?.name ?? 'N/A'"/>
                    </div>
                    <div class="mb-2">
                        <x-modal-row title="Submission Date Time" html="new Date(contentData.feedback_time_submission * 1000)"/>
                    </div>
                </div>
            </div>

        </x-slot>
    </x-modal-container>
    @endif

</x-dashboard-layout>
