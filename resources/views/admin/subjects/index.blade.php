<x-admin-layout>
    <div class="container-fluid">
        <!-- Page Heading -->
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h1 class="h3 mb-0 text-gray-800">Subjects</h1>
            <a href="{{ route('subjects.create') }}" class="btn btn-primary">
                <i class="bi bi-plus-circle me-2"></i>Add New Subject
            </a>
        </div>

        <!-- Subjects Table -->
        <div class="card shadow mb-4">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered" id="subjectsTable">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Teachers</th>
                                <th>Created At</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($subjects as $subject)
                                <tr>
                                    <td>{{ $subject->name }}</td>
                                    <td>{{ $subject->description ?? 'No description' }}</td>
                                    <td>{{ $subject->teachers->count() }}</td>
                                    <td>{{ $subject->created_at->format('M d, Y') }}</td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('subjects.edit', $subject) }}" 
                                               class="btn btn-sm btn-primary me-2">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <form action="{{ route('subjects.destroy', $subject) }}" 
                                                  method="POST" 
                                                  class="d-inline"
                                                  onsubmit="return confirm('Are you sure you want to delete this subject?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger">
                                                    <i class="bi bi-trash"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center">No subjects found.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                
                <!-- Pagination -->
                <div class="mt-4">
                    {{ $subjects->links() }}
                </div>
            </div>
        </div>
    </div>
</x-admin-layout> 