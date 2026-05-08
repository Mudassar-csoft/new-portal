<style>
    .probability-field {
        --probability-accent: #00a8ff;
        --probability-accent-dark: #0082c6;
        --probability-track: #e6eef3;
        --probability-muted: #8d9aa5;
        --probability-progress: 0%;
        width: 100%;
        max-width: 360px;
    }

    .probability-shell {
        position: relative;
        width: 100%;
        padding: 7px 0 0;
    }

    .probability-field .probability-input {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 18px !important;
        min-height: 18px !important;
        padding: 0 !important;
        margin: 0;
        border: 0 !important;
        outline: 0;
        cursor: pointer;
        background: transparent;
    }

    .lead-form .probability-field input.probability-input,
    .probability-field input.probability-input {
        height: 18px !important;
        min-height: 18px !important;
        padding: 0 !important;
        border: 0 !important;
        background-color: transparent !important;
    }

    .probability-field .probability-input::-webkit-slider-runnable-track {
        height: 8px;
        border-radius: 999px;
        background: linear-gradient(
            90deg,
            var(--probability-accent) 0%,
            var(--probability-accent) var(--probability-progress),
            var(--probability-track) var(--probability-progress),
            var(--probability-track) 100%
        );
        box-shadow: inset 0 1px 2px rgba(25, 42, 70, 0.08);
    }

    .probability-field .probability-input::-webkit-slider-thumb {
        -webkit-appearance: none;
        appearance: none;
        width: 22px;
        height: 22px;
        margin-top: -7px;
        border: 4px solid #fff;
        border-radius: 50%;
        background: var(--probability-accent);
        box-shadow: 0 4px 12px rgba(0, 168, 255, 0.35), 0 0 0 1px rgba(0, 130, 198, 0.15);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .probability-field .probability-input::-moz-range-track {
        height: 8px;
        border: 0;
        border-radius: 999px;
        background: var(--probability-track);
        box-shadow: inset 0 1px 2px rgba(25, 42, 70, 0.08);
    }

    .probability-field .probability-input::-moz-range-progress {
        height: 8px;
        border-radius: 999px;
        background: var(--probability-accent);
    }

    .probability-field .probability-input::-moz-range-thumb {
        width: 14px;
        height: 14px;
        border: 4px solid #fff;
        border-radius: 50%;
        background: var(--probability-accent);
        box-shadow: 0 4px 12px rgba(0, 168, 255, 0.35), 0 0 0 1px rgba(0, 130, 198, 0.15);
        transition: transform 0.15s ease, box-shadow 0.15s ease;
    }

    .probability-field .probability-input:hover::-webkit-slider-thumb,
    .probability-field .probability-input:focus::-webkit-slider-thumb {
        transform: scale(1.08);
        box-shadow: 0 5px 16px rgba(0, 168, 255, 0.45), 0 0 0 5px rgba(0, 168, 255, 0.12);
    }

    .probability-field .probability-input:hover::-moz-range-thumb,
    .probability-field .probability-input:focus::-moz-range-thumb {
        transform: scale(1.08);
        box-shadow: 0 5px 16px rgba(0, 168, 255, 0.45), 0 0 0 5px rgba(0, 168, 255, 0.12);
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
        width: 100%;
        margin-top: 5px;
        color: #566a7f;
        font-size: 10px;
        font-weight: 600;
        line-height: 1;
    }

    .probability-scale span {
        flex: 1 1 0;
        text-align: center;
        font-size: 11px !important;
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
        color: #566a7f;
        font-size: 13px;
        font-weight: 600;
        line-height: 1.1;
    }

    .probability-display span {
        color: var(--probability-accent-dark);
        font-weight: 800;
    }

    .probability-shell.has-error .probability-input::-webkit-slider-runnable-track {
        box-shadow: 0 0 0 2px rgba(229, 57, 53, 0.16);
    }

    .probability-shell.has-error .probability-input::-moz-range-track {
        box-shadow: 0 0 0 2px rgba(229, 57, 53, 0.16);
    }

    @media (max-width: 576px) {
        .probability-field {
            max-width: 100%;
        }

        .probability-scale {
            font-size: 9px;
        }
    }
</style>
