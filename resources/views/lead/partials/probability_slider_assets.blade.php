<style>
        :root {
            --dimension-lead-partials-probability-slider-assets-1: 100%;
            --dimension-lead-partials-probability-slider-assets-2: 14px;
            --dimension-lead-partials-probability-slider-assets-3: 18px;
            --dimension-lead-partials-probability-slider-assets-4: 22px;
            --dimension-lead-partials-probability-slider-assets-5: 8px;
            --space-lead-partials-probability-slider-assets-1: 0 !important;
            --color-lead-partials-probability-slider-assets-1: #566a7f;
            --color-lead-partials-probability-slider-assets-2: #fff;
            --color-lead-partials-probability-slider-assets-3: rgba(0, 130, 198, 0.15);
            --color-lead-partials-probability-slider-assets-4: rgba(0, 168, 255, 0.12);
            --color-lead-partials-probability-slider-assets-5: rgba(0, 168, 255, 0.35);
            --color-lead-partials-probability-slider-assets-6: rgba(0, 168, 255, 0.45);
            --color-lead-partials-probability-slider-assets-7: rgba(229, 57, 53, 0.16);
            --color-lead-partials-probability-slider-assets-8: rgba(25, 42, 70, 0.08);
        }

        :root {
            --dimension-lead-partials-probability-slider-assets-1: 100%;
            --dimension-lead-partials-probability-slider-assets-2: 14px;
            --dimension-lead-partials-probability-slider-assets-3: 18px;
            --dimension-lead-partials-probability-slider-assets-4: 22px;
            --dimension-lead-partials-probability-slider-assets-5: 8px;
            --space-lead-partials-probability-slider-assets-1: 0 !important;
            --typo-lead-partials-probability-slider-assets-font-weight-1: 600;
        }0___

    .probability-field {
        --probability-accent: #00a8ff;
        --probability-accent-dark: #0082c6;
        --probability-track: #e6eef3;
        --probability-muted: #8d9aa5;
        --probability-progress: 0%;
        width: var(--dimension-lead-partials-probability-slider-assets-1);
        max-width: 360px;
    }

    .probability-shell {
        position: relative;
        width: var(--dimension-lead-partials-probability-slider-assets-1);
        padding: 7px 0 0;
    }

    .probability-field .probability-input {
        -webkit-appearance: none;
        appearance: none;
        width: var(--dimension-lead-partials-probability-slider-assets-1);
        height: var(--dimension-lead-partials-probability-slider-assets-3) !important;
        min-height: var(--dimension-lead-partials-probability-slider-assets-3) !important;
        padding: var(--space-lead-partials-probability-slider-assets-1);
        margin: 0;
        border: 0 !important;
        outline: 0;
        cursor: pointer;
        background: transparent;
    }

    .lead-form .probability-field input.probability-input,
    .probability-field input.probability-input {
        height: var(--dimension-lead-partials-probability-slider-assets-3) !important;
        min-height: var(--dimension-lead-partials-probability-slider-assets-3) !important;
        padding: var(--space-lead-partials-probability-slider-assets-1);
        border: 0 !important;
        background-color: transparent !important;
    }

    .probability-field .probability-input::-webkit-slider-runnable-track {
        height: var(--dimension-lead-partials-probability-slider-assets-5);
        border-radius: 999px;
        background: linear-gradient(
            90deg,
            var(--probability-accent) 0%,
            var(--probability-accent) var(--probability-progress),
            var(--probability-track) var(--probability-progress),
            var(--probability-track) 100%
        );
        box-shadow: inset 0 1px 2px var(--color-lead-partials-probability-slider-assets-8);
    }

    .probability-field .probability-input::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: var(--dimension-lead-partials-probability-slider-assets-4);
        height: var(--dimension-lead-partials-probability-slider-assets-4);
        margin-top: -7px;
        border: 4px solid var(--color-lead-partials-probability-slider-assets-2);
        border-radius: 50%;
        background: var(--probability-accent);
        box-shadow: 0 4px 12px var(--color-lead-partials-probability-slider-assets-5), 0 0 0 1px var(--color-lead-partials-probability-slider-assets-3);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .probability-field .probability-input::-moz-range-track {
        height: var(--dimension-lead-partials-probability-slider-assets-5);
        border: 0;
        border-radius: 999px;
        background: var(--probability-track);
        box-shadow: inset 0 1px 2px var(--color-lead-partials-probability-slider-assets-8);
    }

    .probability-field .probability-input::-moz-range-progress {
        height: var(--dimension-lead-partials-probability-slider-assets-5);
        border-radius: 999px;
        background: var(--probability-accent);
    }

    .probability-field .probability-input::-moz-range-thumb {
        width: var(--dimension-lead-partials-probability-slider-assets-2);
        height: var(--dimension-lead-partials-probability-slider-assets-2);
        border: 4px solid var(--color-lead-partials-probability-slider-assets-2);
        border-radius: 50%;
        background: var(--probability-accent);
        box-shadow: 0 4px 12px var(--color-lead-partials-probability-slider-assets-5), 0 0 0 1px var(--color-lead-partials-probability-slider-assets-3);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .probability-field .probability-input:hover::-webkit-slider-thumb,
    .probability-field .probability-input:focus::-webkit-slider-thumb {
        transform: scale(1.08);
        box-shadow: 0 5px 16px var(--color-lead-partials-probability-slider-assets-6), 0 0 0 5px var(--color-lead-partials-probability-slider-assets-4);
    }

    .probability-field .probability-input:hover::-moz-range-thumb,
    .probability-field .probability-input:focus::-moz-range-thumb {
        transform: scale(1.08);
        box-shadow: 0 5px 16px var(--color-lead-partials-probability-slider-assets-6), 0 0 0 5px var(--color-lead-partials-probability-slider-assets-4);
    }

    .probability-ticks {
        display: grid;
        grid-template-columns: repeat(31, 1fr);
        align-items: start;
        gap: 0;
        width: calc(100% - 10px);
        margin: 1px 5px 0;
        pointer-events: none;
    }

    .probability-ticks span {
        justify-self: center;
        width: 1px;
        height: 4px;
        border-radius: 999px;
        background: #c9d3da;
        opacity: 0.7;
    }

    .probability-ticks span.is-major {
        height: 7px;
        background: #aebbc4;
        opacity: 0.85;
    }

    .probability-scale {
        display: flex;
        justify-content: space-between;
        width: var(--dimension-lead-partials-probability-slider-assets-1);
        margin-top: 5px;
        color: var(--color-lead-partials-probability-slider-assets-1);
        font-size: 0.625rem;
        font-weight: var(--typo-lead-partials-probability-slider-assets-font-weight-1);
        line-height: 1;
    }

    .probability-scale span {
        flex: 1 1 0;
        text-align: center;
        font-size: 0.6875rem !important;
        white-space: nowrap;
    }

    .probability-scale span:first-child {
        text-align: left;
    }

    .probability-scale span:last-child {
        text-align: right;
    }

    .probability-display {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        margin-top: 8px;
        padding: 4px 9px;
        border: 1px solid rgba(0, 168, 255, 0.16);
        border-radius: 999px;
        background: rgba(0, 168, 255, 0.08);
        color: var(--color-lead-partials-probability-slider-assets-1);
        font-size: 0.8125rem;
        font-weight: var(--typo-lead-partials-probability-slider-assets-font-weight-1);
        line-height: 1.1;
    }

    .probability-display span {
        color: var(--probability-accent-dark);
        font-weight: 800;
    }

    .probability-shell.has-error .probability-input::-webkit-slider-runnable-track {
        box-shadow: 0 0 0 2px var(--color-lead-partials-probability-slider-assets-7);
    }

    .probability-shell.has-error .probability-input::-moz-range-track {
        box-shadow: 0 0 0 2px var(--color-lead-partials-probability-slider-assets-7);
    }

    @media (max-width: 576px) {
        .probability-field {
            max-width: var(--dimension-lead-partials-probability-slider-assets-1);
        }

        .probability-scale {
            font-size: 0.5625rem;
        }
    }
</style>
