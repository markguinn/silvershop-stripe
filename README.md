# SilverShop Stripe Support Module

Stripe uses a little different payment flow than other processors in
that you have to do some clientside javascript work to set it up and
you get a token back instead of credit card processing fields.

This module uses Omnipay's Stripe adapter but overrides SilverShop's
default checkout component to inject the right JavaScript.

## Installation

```
composer require markguinn/silvershop-stripe
```

## License

Copyright 2016 Mark Guinn, All rights reserved.

See LICENSE file. (MIT)
