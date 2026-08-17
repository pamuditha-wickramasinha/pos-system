/* ==========================================================================
   On-screen keyboard for the POS terminal.
   --------------------------------------------------------------------------
   Types into whichever field the cashier last touched, without ever taking
   the focus away from it - so a barcode scanner aimed at #item_search keeps
   working while the keyboard is on screen.
   Values are written with jQuery events (input / keyup / change) because the
   POS rows bind their maths through inline onkeyup / onchange attributes.
   ========================================================================== */
(function ($) {
    'use strict';

    var $keyboard = $('#pos_keyboard');
    if (!$keyboard.length) {
        return;
    }

    var $targetName = $('#pos_kb_target');
    var $targetValue = $('#pos_kb_value');
    var $toggle = $('#pos_kb_toggle');
    var $caps = $('#pos_kb_caps');

    var capsOn = false;
    var $target = $();
    var searchTimer = null;

    /* Fields the keyboard is allowed to type into. Raw <select> is excluded -
       select2 renders its own search input, which is an input and is matched. */
    var TYPEABLE = 'input:not([type=hidden]):not([type=checkbox]):not([type=radio]):not([type=file]):not([readonly]):not([disabled]), textarea';

    function labelFor($el) {
        var el = $el[0];
        if (!el) {
            return 'No field selected';
        }
        if (el.id === 'item_search') {
            return 'Item search';
        }
        if (el.id === 'other_charges') {
            return 'Other charges';
        }
        if (el.id && el.id.indexOf('item_qty_') === 0) {
            return 'Quantity';
        }
        if (el.id && el.id.indexOf('sales_price_') === 0) {
            return 'Price';
        }
        if (el.id && el.id.indexOf('amount_') === 0) {
            return 'Payment amount';
        }
        if (el.id === 'discount_input' || el.id === 'item_discount_input') {
            return 'Discount';
        }
        if (el.id === 'custom_barcode') {
            return 'Barcode';
        }
        if (el.id === 'item_name') {
            return 'Item name';
        }
        if (el.id === 'price') {
            return 'Price';
        }
        if ($el.hasClass('select2-search__field')) {
            return 'Customer search';
        }
        return $el.attr('placeholder') || $el.attr('name') || 'Field';
    }

    function paintValue() {
        var value = $target.length ? String($target.val() || '') : '';
        $targetValue.text(value === '' ? '—' : value);
    }

    function setTarget($el) {
        $target = $el;
        $targetName.text(labelFor($el));
        paintValue();
    }

    /* The row a field belonged to may have been deleted since it was focused. */
    function targetAlive() {
        return $target.length && $.contains(document, $target[0]);
    }

    function ensureTarget() {
        if (!targetAlive()) {
            setTarget($('#item_search'));
        }
        return $target.length > 0;
    }

    /* Fire the same events a physical keystroke would, so the inline
       onkeyup / oninput handlers on the invoice rows recalculate. */
    function notifyTyping() {
        $target.trigger('input').trigger('keyup');

        if ($target.attr('id') === 'item_search') {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                try {
                    $('#item_search').autocomplete('search');
                } catch (e) {
                    /* autocomplete not initialised yet - nothing to do */
                }
            }, 250);
        }
    }

    function typeCharacter(character) {
        if (!ensureTarget()) {
            return;
        }
        var value = String($target.val() || '');
        $target.val(value + (capsOn && character.length === 1 ? character.toUpperCase() : character));
        notifyTyping();
        paintValue();
    }

    function nextField() {
        var $fields = $(TYPEABLE).filter(':visible');
        if (!$fields.length) {
            return;
        }
        var index = $fields.index($target);
        var $next = $fields.eq((index + 1) % $fields.length);
        commitTarget();
        $next.trigger('focus');
        setTarget($next);
    }

    /* onchange handlers (quantity clamping, for one) must run when the cashier
       finishes with a field - not on every key, which is how a real keyboard
       behaves too. */
    function commitTarget() {
        if (targetAlive()) {
            $target.trigger('change');
        }
    }

    function pressEnter() {
        if (!ensureTarget()) {
            return;
        }
        if ($target.attr('id') === 'item_search') {
            clearTimeout(searchTimer);
            /* the view listens for keypress 13 on #item_search: it runs the
               lookup and opens Quick Add when nothing matched */
            $target.trigger($.Event('keypress', { which: 13, keyCode: 13 }));
            paintValue();
            return;
        }
        commitTarget();
        paintValue();
    }

    function runAction(action) {
        if (action === 'caps') {
            capsOn = !capsOn;
            $caps.toggleClass('is-on', capsOn);
            return;
        }
        if (action === 'next') {
            nextField();
            return;
        }
        if (action === 'enter') {
            pressEnter();
            return;
        }
        if (!ensureTarget()) {
            return;
        }
        if (action === 'back') {
            $target.val(String($target.val() || '').slice(0, -1));
        } else if (action === 'clear') {
            $target.val('');
        }
        notifyTyping();
        paintValue();
    }

    /* ------------------------------------------------------- keyboard size */
    /* On a desktop terminal the keyboard is a row of the app shell, so the
       layout already accounts for it. On smaller screens it floats over the
       page, so the body needs matching padding to keep the last controls
       reachable. */
    function syncHeight() {
        var height = $keyboard.outerHeight() || 0;
        var floating = window.getComputedStyle($keyboard[0]).position === 'fixed';

        document.documentElement.style.setProperty('--kb-h', height + 'px');
        $('body').css('padding-bottom', floating ? height + 'px' : '');
    }

    function setCollapsed(collapsed) {
        $keyboard.toggleClass('is-collapsed', collapsed);
        $toggle.html(collapsed
            ? '<i class="fa fa-plus"></i> Show keyboard'
            : '<i class="fa fa-minus"></i> Hide keyboard');
        syncHeight();
    }

    /* --------------------------------------------------------------- wiring */
    /* pointerdown covers mouse, pen and touch; preventing it is what keeps the
       caret - and any barcode scanner input - in the field being typed into. */
    $keyboard.on('pointerdown mousedown', '.pos-key, .pos-kb-toggle', function (event) {
        event.preventDefault();
    });

    $keyboard.on('click', '.pos-key', function () {
        var $key = $(this);
        var character = $key.attr('data-key');

        if (typeof character !== 'undefined') {
            typeCharacter(character);
            return;
        }
        runAction($key.attr('data-action'));
    });

    $toggle.on('click', function () {
        setCollapsed(!$keyboard.hasClass('is-collapsed'));
    });

    $(document).on('focusin', TYPEABLE, function () {
        var $field = $(this);
        if ($field.closest('.pos-keyboard').length) {
            return;
        }
        if (!$target.is($field)) {
            commitTarget();
        }
        setTarget($field);
    });

    /* keep the readout honest when the cashier types on the real keyboard */
    $(document).on('input keyup', TYPEABLE, function () {
        if ($target.is(this)) {
            paintValue();
        }
    });

    $(window).on('resize orientationchange', syncHeight);

    if (window.ResizeObserver) {
        new ResizeObserver(syncHeight).observe($keyboard[0]);
    }

    $(function () {
        setCollapsed(false);          /* always starts open, on every device */
        setTarget($('#item_search'));
        syncHeight();
    });

})(jQuery);
