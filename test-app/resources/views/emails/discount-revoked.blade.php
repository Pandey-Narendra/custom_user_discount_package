<x-mail::message>
# Discount Removed

Hi {{ $user->name }},

The discount **{{ $discount->code }}** has been removed from your account.

If you have any questions, feel free to contact support.

<x-mail::button :url="route('discounts.test')">
    View Discounts
</x-mail::button>

The Narendra Team
</x-mail::message>