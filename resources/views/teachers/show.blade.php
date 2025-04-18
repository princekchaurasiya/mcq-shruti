<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ __('Teacher Details') }}
            </h2>
            <a href="{{ route('teachers.index') }}" class="bg-gray-500 hover:bg-gray-700 text-white font-bold py-2 px-4 rounded">
                Back to List
            </a>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <div class="mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Personal Information</h3>
                        <div class="mt-4 grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <p class="text-sm font-medium text-gray-500">Name</p>
                                <p class="mt-1">{{ $teacher->user->name }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Email</p>
                                <p class="mt-1">{{ $teacher->user->email }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Phone</p>
                                <p class="mt-1">{{ $teacher->phone ?? 'Not provided' }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Status</p>
                                <p class="mt-1">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                        {{ $teacher->status === 'active' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800' }}">
                                        {{ ucfirst($teacher->status) }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-gray-200 pt-6 mb-6">
                        <h3 class="text-lg font-medium text-gray-900">Professional Information</h3>
                        <div class="mt-4">
                            <div class="mb-4">
                                <p class="text-sm font-medium text-gray-500">Subject Specialization</p>
                                <p class="mt-1">{{ $teacher->subject_specialization }}</p>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-gray-500">Qualifications</p>
                                <p class="mt-1 whitespace-pre-line">{{ $teacher->qualifications }}</p>
                            </div>
                        </div>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <a href="{{ route('teachers.edit', $teacher) }}" 
                           class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                            Edit Teacher
                        </a>
                        <form action="{{ route('teachers.destroy', $teacher) }}" method="POST" class="inline">
                            @csrf
                            @method('DELETE')
                            <button type="submit" 
                                    class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded"
                                    onclick="return confirm('Are you sure you want to delete this teacher?')">
                                Delete Teacher
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout> 