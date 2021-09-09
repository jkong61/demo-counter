<x-dashboard-layout>
    <x-slot name="title">
        {{__('Counter User Overview')}}
    </x-slot>

    <x-filter-form action="{{ route('counteruser.index') }}">
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 min-w-full">
            <div class="mb-4">
                <x-label for="counterusername">
                    Counter User Name
                </x-label>
                <input type="text" class="block mt-1 w-full form-control"
                    id="counterusername" name="counterusername" placeholder="Enter User Name">
                @error('counterusername')
                    <div class="text-red-400 text-xs">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <x-label for="identifier">
                    ID Number
                </x-label>
                <input type="text" class="block mt-1 w-full form-control"
                    id="identifier" name="identifier" placeholder="Enter User ID">
                @error('identifier')
                    <div class="text-red-400 text-xs">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <x-label for="ordering">
                    Display Ordering
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
        </div>
    </x-filter-form>

    @if (count($counter_users) <= 0)
        <span class="text-gray-900 dark:text-gray-100">No Counter Users recorded.</span>
    @else
        <x-table>
            <x-slot name="thead">
                <tr>
                    <x-table-header-col>
                        No.
                    </x-table-header-col>
                    <x-table-header-col>
                        Full Name
                    </x-table-header-col>
                    <x-table-header-col>
                        Display Name
                    </x-table-header-col>
                    <x-table-header-col>
                        Last Updated
                    </x-table-header-col>
                    <x-table-header-col class="relative px-6 py-3">
                        <span class="sr-only">Actions</span>
                    </x-table-header-col>
                </tr>

            </x-slot>

            <x-slot name="tbody">
                @foreach ($counter_users as $counter_user)
                <tr>
                    <x-table-body-col class="text-xs">
                        {{$counter_user->id}}
                    </x-table-body-col>
                    <x-table-body-col>
                        <div class="text-sm font-semibold text-gray-900">
                            {{ $counter_user->name }}
                        </div>
                    </x-table-body-col>
                    <x-table-body-col class="text-sm">
                        {{$counter_user->sname}}
                    </x-table-body-col>
                    <x-table-body-col class="text-sm">
                        {{$counter_user->updated_at}}
                    </x-table-body-col>
                    <x-table-body-col class="text-left space-x-2">
                        <a href="{{ route('counteruser.show', $counter_user) }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-900">View</a>
                    </x-table-body-col>
                </tr>
                @endforeach
            </x-slot>

            <x-slot name="pagination">
                {{ $counter_users->appends(request()->query())->links() }}
            </x-slot>
        </x-table>
    @endif

</x-dashboard-layout>
