
<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Admin Dashboard') }}
        </h2>
    </x-slot>
    @vite(['resources/css/style.css'])
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    {{ __("You're logged in as an Administrator") }}
                <div class="table-container">
                    <table class="user-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Created Date</th>
                                <th>role</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $user->id }}</td>
                                    <td>{{ $user->name }}</td>
                                    <td>{{ $user->email }}</td>
                                    <td>{{ $user->created_at}}</td>
                                    <td>{{ $user->role }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                    <div class='butona'>
                        <button class="btn-create">Create</button>
                        <button class="btn-edit">edit</button>
                        <button class="btn-update">update</button>
                        <button class="btn-delete">delete</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
