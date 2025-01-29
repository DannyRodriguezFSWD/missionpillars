<div class="print sticker" style="margin: auto; padding: 0 .2in; font-size: 1rem;">
    <style>
        p, h5 {
            margin: 0;
            padding: 0;
        }
        
        @media print {
            @page {
                size: landscape;
                margin: 0 !important;
            }
            
            body {
                visibility: hidden;
            }
            .print.sticker {
                visibility: visible;
                position: absolute;
                left: 0;
                top: 0;
                color: black !important;
            }
            
            @media print and (-webkit-min-device-pixel-ratio:0) {
                .print.sticker {
                    visibility: visible;
                    position: absolute;
                    left: 0;
                    top: 0;
                    color: black !important;
                    -webkit-print-color-adjust: exact;
                }
                
            }
        }
    </style>
    
    <div id="printContainer"></div>
</div>

<div id="printTemplate" class="d-none">
    <div>
        <h5 class="name"></h5>
        <small class="date">&nbsp;</small>
    </div>
    <footer></footer>
</div>
