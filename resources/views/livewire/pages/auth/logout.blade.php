<?php

use App\Livewire\Actions\Logout;
use Livewire\Volt\Component;

new class extends Component {
    /**
     * Log the user out of the application.
     */
    public function logout(): void
    {
        auth()->guard('web')->logout();
        
        session()->invalidate();
        session()->regenerateToken();

        $this->redirect('/', navigate: true);
    }
}; ?>

<div>
    <form method="POST" action="{{ route('logout') }}" class="d-inline">
        @csrf
        <button type="submit" class="btn btn-link text-danger p-0">
            <i class="bi bi-box-arrow-right me-2"></i> Logout
        </button>
    </form>
</div> 