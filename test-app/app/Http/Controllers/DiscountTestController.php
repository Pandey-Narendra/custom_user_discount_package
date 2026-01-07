<?php

namespace App\Http\Controllers;

use Acme\UserDiscounts\Facades\UserDiscount;
use Acme\UserDiscounts\Models\Discount;
use Acme\UserDiscounts\Models\DiscountAudit;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DiscountTestController extends Controller
{
    /**
     * Display the main test dashboard
     */
    public function index(Request $request): View
    {
        // Get or create a test user safely
        $user = auth()->user() ?? User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        // Paginated discounts (10 per page)
        $discounts = Discount::with('userDiscounts')
            ->orderByDesc('id')
            ->paginate(10);

        // Paginated audits (10 per page) - separate from discounts
        $audits = DiscountAudit::with(['user', 'discount']) // Eager load relations for performance
            ->latest()
            ->paginate(10);

        return view('discounts-test', compact('user', 'discounts', 'audits'));
    }

    /**
     * Create a new discount
     */
    public function createDiscount(Request $request)
    {
        $validated = $request->validate([
            'name'       => 'required|string|max:255',
            'code'       => 'required|string|unique:discounts,code',
            'percentage' => 'required|numeric|min:0|max:100',
            'user_limit' => 'required|integer|min:1',
            'is_active'  => 'sometimes|boolean',
            'starts_at'  => 'nullable|date',
            'ends_at'    => 'nullable|date|after_or_equal:starts_at',
        ]);

        // Set default for is_active if not provided
        $validated['is_active'] = $request->has('is_active');

        Discount::create($validated);

        return redirect()->back()->with('success', 'Discount created successfully!');
    }

    /**
     * Assign a discount to the current test user
     */
    public function assign(Request $request, Discount $discount)
    {
        $user = auth()->user() ?? User::where('email', 'test@example.com')->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Test user not found.');
        }

        try {
            UserDiscount::assign($user, $discount);
            return redirect()->back()->with('success', "Discount '{$discount->code}' assigned successfully!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to assign discount: ' . $e->getMessage());
        }
    }

    /**
     * Revoke a discount from the current test user
     */
    public function revoke(Request $request, Discount $discount)
    {
        $user = auth()->user() ?? User::where('email', 'test@example.com')->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Test user not found.');
        }

        try {
            UserDiscount::revoke($user, $discount);
            return redirect()->back()->with('success', "Discount '{$discount->code}' revoked successfully!");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to revoke discount: ' . $e->getMessage());
        }
    }

    /**
     * Apply all eligible discounts to a cart subtotal
     */
    public function apply(Request $request)
    {
        $validated = $request->validate([
            'subtotal' => 'required|numeric|min:0.01', // Prevent zero/negative
        ]);

        $user = auth()->user() ?? User::where('email', 'test@example.com')->first();

        if (!$user) {
            return redirect()->back()->with('error', 'Test user not found.');
        }

        $subtotal = (float) $validated['subtotal'];

        try {
            $result = UserDiscount::apply($user, $subtotal);

            return redirect()->back()
                ->with('apply_result', $result)
                ->with('subtotal', $subtotal)
                ->with('success', 'Discounts applied successfully!');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('subtotal', $subtotal)
                ->with('error', 'Failed to apply discounts: ' . $e->getMessage());
        }
    }
}