<x-mail::message>
# Congratulations, {{ $user->name }}! 🎉

You've just been granted an exclusive discount!

### Discount Details
- **Code:** `{{ $code }}`
- **Discount:** {{ $percentage }}% off
- **Name:** {{ $discount->name }}

Use this code at checkout to enjoy your savings.

<x-mail::button :url="route('discounts.test')">
    Shop Now
</x-mail::button>

Thanks for being awesome!  
The Narendra Team
</x-mail::message>