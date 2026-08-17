{{-- On-screen keyboard for touch terminals. Behaviour lives in theme/js/pos-keypad.js --}}
<div class="pos-keyboard" id="pos_keyboard">

    <div class="pos-kb-bar">
        <span class="pos-kb-label">Typing into:</span>
        <b class="pos-kb-target" id="pos_kb_target">Item search</b>
        <span class="pos-kb-value" id="pos_kb_value">&mdash;</span>
        <button type="button" class="pos-kb-toggle" id="pos_kb_toggle">
            <i class="fa fa-minus"></i> Hide keyboard
        </button>
    </div>

    <div class="pos-kb-body">

        {{-- letters + numbers --}}
        <div class="pos-kb-letters">
            <div class="pos-kb-row">
                @foreach (['1','2','3','4','5','6','7','8','9','0'] as $n)
                    <button type="button" class="pos-key" data-key="{{ $n }}">{{ $n }}</button>
                @endforeach
                <button type="button" class="pos-key pos-key-back w15" data-action="back"><i
                        class="fa fa-arrow-left"></i><span class="pos-key-txt">&nbsp;Back</span></button>
            </div>

            <div class="pos-kb-row">
                @foreach (['q','w','e','r','t','y','u','i','o','p'] as $l)
                    <button type="button" class="pos-key" data-key="{{ $l }}">{{ strtoupper($l) }}</button>
                @endforeach
                <button type="button" class="pos-key pos-key-fn w15" data-action="clear"><i
                        class="fa fa-eraser"></i><span class="pos-key-txt">&nbsp;Clear</span></button>
            </div>

            <div class="pos-kb-row">
                @foreach (['a','s','d','f','g','h','j','k','l'] as $l)
                    <button type="button" class="pos-key" data-key="{{ $l }}">{{ strtoupper($l) }}</button>
                @endforeach
                <button type="button" class="pos-key pos-key-enter w20" data-action="enter"><i
                        class="fa fa-level-down fa-rotate-90"></i><span class="pos-key-txt">&nbsp;Enter</span></button>
            </div>

            <div class="pos-kb-row">
                <button type="button" class="pos-key pos-key-fn w15" id="pos_kb_caps" data-action="caps"><i
                        class="fa fa-arrow-up"></i><span class="pos-key-txt">&nbsp;Caps</span></button>
                @foreach (['z','x','c','v','b','n','m'] as $l)
                    <button type="button" class="pos-key" data-key="{{ $l }}">{{ strtoupper($l) }}</button>
                @endforeach
                <button type="button" class="pos-key" data-key="-">-</button>
                <button type="button" class="pos-key" data-key=".">.</button>
                <button type="button" class="pos-key pos-key-fn w15" data-action="next"><i
                        class="fa fa-long-arrow-right"></i><span class="pos-key-txt">&nbsp;Next</span></button>
            </div>

            <div class="pos-kb-row">
                <button type="button" class="pos-key w60" data-key=" ">space</button>
            </div>
        </div>

        {{-- number pad --}}
        <div class="pos-kb-num">
            <button type="button" class="pos-key" data-key="7">7</button>
            <button type="button" class="pos-key" data-key="8">8</button>
            <button type="button" class="pos-key" data-key="9">9</button>
            <button type="button" class="pos-key pos-key-back" data-action="back"><i class="fa fa-arrow-left"></i></button>

            <button type="button" class="pos-key" data-key="4">4</button>
            <button type="button" class="pos-key" data-key="5">5</button>
            <button type="button" class="pos-key" data-key="6">6</button>
            <button type="button" class="pos-key pos-key-fn" data-action="clear">C</button>

            <button type="button" class="pos-key" data-key="1">1</button>
            <button type="button" class="pos-key" data-key="2">2</button>
            <button type="button" class="pos-key" data-key="3">3</button>
            <button type="button" class="pos-key pos-key-enter tall" data-action="enter"><i class="fa fa-level-down fa-rotate-90"></i></button>

            <button type="button" class="pos-key" data-key="0">0</button>
            <button type="button" class="pos-key" data-key="00">00</button>
            <button type="button" class="pos-key" data-key=".">.</button>
        </div>

    </div>
</div>
