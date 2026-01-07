<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Discounts Package – Test Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        .hero-icon { width: 1.25rem; height: 1.25rem; }
    </style>
</head>
<body class="bg-gradient-to-br from-indigo-50 via-white to-purple-50 min-h-screen py-10 px-4">
<div class="max-w-7xl mx-auto">

    <!-- Header -->
    <div class="text-center mb-12">
        <h1 class="text-5xl font-extrabold text-transparent bg-clip-text bg-gradient-to-r from-indigo-600 to-purple-600 mb-4">
            Laravel User Discounts – Test Dashboard
        </h1>
        <p class="text-lg text-gray-600">Create, assign, apply, and audit user-level discounts with full stacking & concurrency safety</p>
    </div>

    <!-- Success Alert -->
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-800 p-4 rounded-r-lg shadow-md mb-8 flex items-center animate-pulse">
            <svg class="w-6 h-6 mr-3 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">{{ session('success') }}</span>
        </div>
    @endif

    <!-- Current User Card -->
    <div class="bg-white rounded-xl shadow-lg p-6 mb-8 border border-indigo-100">
        <div class="flex items-center">
            <div class="bg-indigo-100 rounded-full p-4 mr-5">
                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
            </div>
            <div>
                <h2 class="text-2xl font-bold text-gray-800">Current Test User</h2>
                <p class="text-lg text-gray-600">{{ $user->name }} <span class="text-sm font-mono bg-gray-200 px-2 py-1 rounded">ID: {{ $user->id }}</span></p>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-8 mb-10">

        <!-- Create Discount Form -->
        <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-200">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-8 h-8 text-indigo-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Create New Discount
            </h2>
            <form action="{{ route('discounts.test.create') }}" method="POST" class="space-y-6">
                @csrf
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Discount Name</label>
                        <input type="text" name="name" placeholder="e.g. Summer Sale" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Discount Code</label>
                        <input type="text" name="code" placeholder="e.g. SUMMER20" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Percentage (%)</label>
                        <input type="number" step="0.01" name="percentage" placeholder="e.g. 20.00" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Max Uses Per User</label>
                        <input type="number" name="user_limit" value="1" min="1" required
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">Start Date (Optional)</label>
                        <input type="date" name="starts_at"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>

                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">End Date (Optional)</label>
                        <input type="date" name="ends_at"
                               class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-500 focus:border-transparent transition">
                    </div>
                </div>

                <div class="flex items-center justify-between">
                    <label class="flex items-center cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="mr-3 h-5 w-5 text-indigo-600 rounded focus:ring-indigo-500">
                        <span class="text-lg font-medium text-gray-700">Make this discount active</span>
                    </label>

                    <button type="submit"
                            class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-bold py-3 px-10 rounded-lg shadow-lg hover:shadow-xl transform hover:scale-105 transition duration-200">
                        Create Discount
                    </button>
                </div>
            </form>
        </div>

        <!-- Apply Discounts to Cart -->
        <div class="bg-white rounded-xl shadow-lg p-8 border border-gray-200">
            <h2 class="text-3xl font-bold text-gray-800 mb-6 flex items-center">
                <svg class="w-8 h-8 text-green-600 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                Apply Discounts to Cart
            </h2>

            <form action="{{ route('discounts.test.apply') }}" method="POST" class="mb-8">
                @csrf
                <div class="flex items-end gap-6">
                    <div class="flex-1">
                        <label class="block text-lg font-semibold text-gray-700 mb-3">Cart Subtotal ($)</label>
                        <input type="number" step="0.01" name="subtotal"
                               value="{{ old('subtotal', session('subtotal', 150.00)) }}"
                               required
                               class="w-full px-6 py-4 text-2xl font-bold border-2 border-gray-300 rounded-xl focus:ring-4 focus:ring-green-300 focus:border-green-500 transition">
                    </div>
                    <button type="submit"
                            class="bg-gradient-to-r from-green-600 to-emerald-600 text-white font-bold py-5 px-12 rounded-xl shadow-xl hover:shadow-2xl transform hover:scale-105 transition duration-200 text-xl">
                        Apply Discounts
                    </button>
                </div>
            </form>

            @if(session('apply_result'))
                <div class="bg-gradient-to-br from-green-50 to-emerald-50 border-2 border-green-400 rounded-2xl p-8 shadow-xl">
                    <h3 class="text-3xl font-bold text-green-800 mb-6 text-center">
                        Discount Calculation Result
                    </h3>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-8">
                        <div class="bg-white rounded-xl p-6 text-center shadow-lg">
                            <p class="text-gray-600 text-lg mb-2">Original Subtotal</p>
                            <p class="text-4xl font-bold text-gray-800">${{ number_format(session('subtotal'), 2) }}</p>
                        </div>
                        <div class="bg-white rounded-xl p-6 text-center shadow-lg">
                            <p class="text-gray-600 text-lg mb-2">Total Discount Applied</p>
                            <p class="text-5xl font-extrabold text-green-600">
                                -${{ number_format(session('apply_result')['total_discount'], 2) }}
                            </p>
                        </div>
                    </div>

                    <div class="bg-indigo-600 text-white rounded-xl p-6 text-center shadow-xl mb-10">
                        <p class="text-2xl mb-2">Final Price After Discounts</p>
                        <p class="text-6xl font-extrabold">
                            ${{ number_format(session('subtotal') - session('apply_result')['total_discount'], 2) }}
                        </p>
                    </div>

                    @if(session('apply_result')['applied']->isNotEmpty())
                        <div>
                            <h4 class="text-2xl font-bold text-gray-800 mb-6 text-center">Applied Discount Codes</h4>
                            <div class="space-y-5">
                                @foreach(session('apply_result')['applied'] as $item)
                                    @php
                                        $remaining = $item['discount']->user_limit - $item['usage_after'];
                                    @endphp
                                    <div class="bg-white rounded-xl p-6 shadow-lg border-l-8 border-indigo-500 flex justify-between items-center hover:shadow-xl transition">
                                        <div class="flex items-center">
                                            <div class="bg-indigo-100 rounded-full p-3 mr-5">
                                                <svg class="w-8 h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                                </svg>
                                            </div>
                                            <div>
                                                <div class="text-2xl font-bold text-indigo-700">{{ $item['discount']->code }}</div>
                                                <div class="text-lg text-gray-600">{{ $item['discount']->name }} • {{ $item['discount']->percentage }}% off</div>
                                            </div>
                                        </div>
                                        <div class="text-right">
                                            <div class="text-3xl font-extrabold text-green-600">
                                                -${{ number_format($item['amount'], 2) }}
                                            </div>
                                            <div class="text-sm text-gray-600 mt-2">
                                                Usage: {{ $item['usage_before'] }} → {{ $item['usage_after'] }}
                                            </div>
                                            <div class="mt-3">
                                                @if($remaining <= 0)
                                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-red-100 text-red-800">
                                                        No uses remaining
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-4 py-2 rounded-full text-sm font-bold bg-green-100 text-green-800">
                                                        {{ $remaining }} use{{ $remaining > 1 ? 's' : '' }} remaining
                                                    </span>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @else
                        <div class="text-center py-12">
                            <p class="text-2xl text-gray-500">No eligible discounts were applied to this order.</p>
                            <p class="text-lg text-gray-400 mt-4">Try assigning some active discounts first!</p>
                        </div>
                    @endif
                </div>
            @endif
        </div>
    </div>

    <!-- All Discounts Table with Pagination -->
    <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200 mb-10">
        <div class="bg-gradient-to-r from-indigo-600 to-purple-600 text-white p-6">
            <h2 class="text-3xl font-bold flex items-center">
                <svg class="w-9 h-9 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                All Discounts ({{ $discounts->total() }} total • Page {{ $discounts->currentPage() }} of {{ $discounts->lastPage() }})
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                <tr>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">ID</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Name / Code</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Percent</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Limit</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Status</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Eligible?</th>
                    <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Actions</th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                @forelse($discounts as $discount)
                    @php
                        $assignment = $discount->userDiscounts()->where('user_id', $user->id)->first();
                        $eligible = $assignment && \Acme\UserDiscounts\Facades\UserDiscount::eligibleFor($user, $discount);
                    @endphp
                    <tr class="hover:bg-gray-50 transition {{ $discount->is_currently_active ? '' : 'bg-red-50' }}">
                        <td class="px-6 py-4 font-mono text-sm">{{ $discount->id }}</td>
                        <td class="px-6 py-4">
                            <div class="font-semibold text-gray-900">{{ $discount->name }}</div>
                            <div class="text-sm text-indigo-600 font-mono bg-indigo-50 px-2 py-1 rounded mt-1 inline-block">{{ $discount->code }}</div>
                        </td>
                        <td class="px-6 py-4 font-bold text-lg text-indigo-600">{{ $discount->percentage }}%</td>
                        <td class="px-6 py-4">{{ $discount->user_limit }}</td>
                        <td class="px-6 py-4">
                            <div class="flex flex-col space-y-1">
                                @if($discount->is_currently_active)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        Active
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        Inactive/Expired
                                    </span>
                                @endif
                                @if($assignment?->revoked_at)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-orange-100 text-orange-800">
                                        Revoked
                                    </span>
                                @endif
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <span class="font-bold text-lg {{ $eligible ? 'text-green-600' : 'text-red-600' }}">
                                {{ $eligible ? 'YES' : 'NO' }}
                            </span>
                            @if($assignment)
                                <div class="text-sm text-gray-600 mt-1">
                                    Uses: {{ $assignment->usage_count }} / {{ $discount->user_limit }}
                                </div>
                            @endif
                        </td>
                        <td class="px-6 py-4 space-x-3">
                            <form action="{{ route('discounts.test.assign', $discount) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="bg-blue-600 hover:bg-blue-700 text-white font-medium py-2 px-5 rounded-lg shadow transition transform hover:scale-105">
                                    Assign
                                </button>
                            </form>
                            <form action="{{ route('discounts.test.revoke', $discount) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit"
                                        class="bg-red-600 hover:bg-red-700 text-white font-medium py-2 px-5 rounded-lg shadow transition transform hover:scale-105">
                                    Revoke
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-gray-500 text-lg">
                            No discounts created yet. Create one above!
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
            {{ $discounts->onEachSide(1)->links() }}
        </div>
    </div>

    <!-- Audit Log -->
    <div class="bg-white rounded-xl shadow-xl overflow-hidden border border-gray-200">
        <div class="bg-gradient-to-r from-purple-600 to-pink-600 text-white p-6">
            <h2 class="text-3xl font-bold flex items-center">
                <svg class="w-9 h-9 mr-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Recent Audit Log ({{ $audits->total() }} total)
            </h2>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-50 border-b-2 border-gray-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Time</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">User</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Discount</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Action</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">Usage Change</th>
                        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700">IP Address</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($audits as $audit)
                        <tr class="hover:bg-purple-50 transition">
                            <td class="px-6 py-4 font-mono text-sm">
                                {{ $audit->applied_at->format('M d, Y H:i:s') }}
                            </td>
                            <td class="px-6 py-4 font-medium">
                                {{ $audit->user?->name ?? 'Unknown User' }}
                            </td>
                            <td class="px-6 py-4">
                                <code class="bg-gray-100 px-3 py-1 rounded text-sm font-medium">
                                    {{ $audit->discount?->code ?? 'Deleted Discount' }}
                                </code>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-4 py-2 rounded-full text-xs font-bold
                                    {{ $audit->action === 'assign' ? 'bg-blue-100 text-blue-800' :
                                       ($audit->action === 'revoke' ? 'bg-red-100 text-red-800' :
                                       'bg-green-100 text-green-800') }}">
                                    {{ ucfirst($audit->action) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-center font-mono text-sm">
                                {{ $audit->old_usage }} → {{ $audit->new_usage }}
                            </td>
                            <td class="px-6 py-4 font-mono text-sm text-gray-600">
                                {{ $audit->ip_address }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-gray-500 text-lg">
                                <div class="flex flex-col items-center">
                                    <svg class="w-16 h-16 text-gray-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                    <p class="font-medium">No audit records yet</p>
                                    <p class="text-sm text-gray-400 mt-2">Create, assign, or apply a discount to see logs here</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination Links -->
        @if($audits->hasPages())
            <div class="px-6 py-4 bg-gray-50 border-t border-gray-200">
                {{ $audits->onEachSide(1)->links() }}
            </div>
        @endif
    </div>

</div>
</body>
</html>