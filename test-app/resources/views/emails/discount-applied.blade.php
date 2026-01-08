<x-mail::message>
# Discount Applied Successfully! ✅

Hi {{ $user->name }},

Your discount **{{ $discount->code }}** ({{ $discount->percentage }}%) has been successfully applied!

You saved **${{ number_format($amount, 2) }}** on this order.

Thank you for shopping with us!

<x-mail::button :url="route('discounts.test')">
    View Order
</x-mail::button>

The Narendra Team
</x-mail::message>