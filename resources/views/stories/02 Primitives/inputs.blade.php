@storybook([
    'layout' => 'fullscreen',
    'name' => 'Inputs',
    'args' => [
        'label' => 'First name',
        'name' => 'username',
        'id' => '',
        'type' => 'text',
        'value' => '',
        'placeholder' => 'Placeholder',
        'disabled' => false,
        'required' => false,
        'error' => '',
        'hint' => 'Hint',
        'note' => 'Note',
        'inputmode' => '',
        'pattern' => '',
        'autocomplete' => '',
        'autofocus' => false,
        'form' => '',
        'list' => '',
        'max' => '',
        'maxlength' => '',
        'min' => '',
        'minlength' => '',
        'multiple' => false,
        'readonly' => false,
        'step' => '',
    ],
])
<x-docs-page title="Inputs">
    <div class="block space-y-10">
        <div class="w-1/2">
            <x-vui-form-input :label="$label ?? ''" :name="$name ?? ''" :id="$id ?? ''" :type="$type ?? ''" :value="$value ?? ''"
                :placeholder="$placeholder ?? ''" :disabled="$disabled ?? ''" :required="$required ?? ''" :error="$error ?? ''" :hint="$hint ?? ''"
                :note="$note ?? ''" :inputmode="$inputmode ?? ''" :pattern="$pattern ?? ''" :autocomplete="$autocomplete ?? ''" :autofocus="$autofocus ?? ''"
                :form="$form ?? ''" :list="$list ?? ''" :max="$max ?? ''" :maxlength="$maxlength ?? ''" :min="$min ?? ''"
                :minlength="$minlength ?? ''" :multiple="$multiple ?? ''" :readonly="$readonly ?? ''" :step="$step ?? ''" />
        </div>
    </div>
</x-docs-page>
