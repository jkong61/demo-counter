@php
    $is_user_selection = request()->get('selection') == 'user';
@endphp

<x-dashboard-layout>
    <x-slot name="title">
        Overview
    </x-slot>

    <div class="min-w-full sm:rounded-lg bg-gray-50 dark:bg-gray-800 p-2 px-4 shadow-sm">
        <div class="my-3 text-gray-800 dark:text-gray-400 text-sm">Rating Breakdown</div>
        <div class="grid grid-cols-3 sm:grid-cols-6">
            @foreach ($ratings['rating_group'] as $key=>$rating)
                <div class="text-gray-800 dark:text-gray-100">Rating {{$key}} : <span class="font-bold">{{ $rating->count() }}</span></div>
            @endforeach
            <div class="text-gray-800 dark:text-gray-100 font-bold">Total feedback: {{ $ratings['total_feedback'] }}</div>
        </div>
    </div>

    <x-filter-form action="{{ route('dashboard') }}">
        <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 min-w-full">
            <div class="mb-4">
                <x-label for="selection">
                    Rating Selection
                </x-label>
                <select class="block mt-1 w-full form-control"
                    id="selection" name="selection">
                    <option {{ request()->get('selection') == 'counter' ? "selected":"" }} value="counter">Counter Ratings</option>
                    <option {{ request()->get('selection') == 'user' ? "selected":"" }} value="user">Counter User Ratings</option>
                </select>
                @error('selection')
                    <div class="text-red-400 text-xs">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <x-label for="branch">
                    Branch
                </x-label>
                <select class="block mt-1 w-full form-control"
                    id="branch" name="branch">
                    <option value="">All</option>
                    @foreach ($branches as $branch)
                        <option {{ request()->get('branch') == $branch->id ? "selected" : "" }} 
                            value={{$branch->id}}
                        >{{$branch->name}}</option>
                    @endforeach
                </select>
                @error('branch')
                    <div class="text-red-400 text-xs">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <x-label for="ordering">
                    Order
                </x-label>
                <select class="block mt-1 w-full form-control"
                    id="ordering" name="ordering">
                    <option {{ request()->get('ordering') == '0' ? "selected":"" }} value="0">Ascending</option>
                    <option {{ request()->get('ordering') == '1' ? "selected":"" }} value="1">Decending</option>
                </select>
                @error('ordering')
                    <div class="text-red-400 text-xs">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <x-label for="identifier">
                    Number
                </x-label>
                <input type="text" class="block mt-1 w-full form-control"
                    id="identifier" name="identifier">
                @error('identifier')
                    <div class="text-red-400 text-xs">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </x-filter-form>

    @if (count($models) <= 0)
        <span class="text-gray-900 dark:text-gray-100">No feedbacks recorded.</span>
    @else
    <x-table>
        <x-slot name="thead">
            <tr>
                <x-table-header-col>
                    @if ($is_user_selection)
                        User No.
                    @else
                        Counter No.
                    @endif
                </x-table-header-col>
                @if ($is_user_selection)
                <x-table-header-col>
                    Branch
                </x-table-header-col>
                @endif
                <x-table-header-col>
                @if (!$is_user_selection)
                    Branch
                @else
                    Name
                @endif
                </x-table-header-col>
                <x-table-header-col>
                    Average Rating
                </x-table-header-col>
                <x-table-header-col>
                    Feedback Count
                </x-table-header-col>
                <x-table-header-col class="relative px-6 py-3">
                    <span class="sr-only">Actions</span>
                </x-table-header-col>
            </tr>    
        </x-slot>

        <x-slot name="tbody">
            @foreach ($models as $model)
            <tr>
                <x-table-body-col class="text-xs">
                    {{$model->number}}
                </x-table-body-col>
                @if ($is_user_selection)
                <x-table-body-col class="text-xs">
                    {{$model->branch->name}}
                </x-table-body-col>
                @endif
                <x-table-body-col class="text-xs">
                @if (!$is_user_selection)
                    {{$model->branch->name}}
                @else
                    {{$model->name}}
                @endif
                </x-table-body-col>
                <x-table-body-col class="text-xs">
                    {{ is_null($model->feedback_avg_rating) ? 'N/A' : number_format($model->feedback_avg_rating, 2) }}
                </x-table-body-col>
                <x-table-body-col class="text-xs">
                    {{ $model->feedback_count }}
                </x-table-body-col>
                <x-table-body-col class="text-left space-x-2">
                    <a href="{{ request()->get('selection') == 'user' ? route('dashboard.view_user', $model) : route('dashboard.view_counter', $model) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">View</a>
                </x-table-body-col>
            </tr>
            @endforeach

        </x-slot>

        <x-slot name="pagination">
            {{ $models->appends(request()->query())->links() }}
        </x-slot>
    </x-table>
    @endif

</x-dashboard-layout>
