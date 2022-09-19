<template>
  <div class="autocomplete-field position-relative" >

    <input
      type="text"
      class="form-control form-control-sm"
      v-bind="$attrs"
      :value="modelValue"
      @input="onInputEventHandler($event, type, column)"
    />

    <div class="position-absolute mt-n1 bg-white p-1 mx-1 w-100" v-if="autocomplete.length > 0" style="z-index: 10000;">
      <ul class="list-unstyled pl-0 mb-0">
        <li class="small" v-for="value in autocomplete">
          <a href="#" @click.prevent="setAutocompleteClick(type, column, value)">
            {{ value }}
          </a>
        </li>
      </ul>
    </div>

  </div>
</template>

<script>
const axios = require('axios').default;

export default {
  name: 'AutoCompleteField',

  inheritAttrs: false,

  props: ['modelValue', 'type', 'column'],

  data() {
    return {
      autocomplete: [],
    }
  },

  mounted() {
    this.emitInterface();
  },

  methods: {
    onInputEventHandler($event, type, field) {
      const { target: { value } } = $event;
      this.$emit('update:modelValue', value);

      if (value === '' || value === null) {
        this.autocomplete = [];
        return;
      }

      setTimeout(() => {
        (async () => {
          await axios
            .get(`http://localhost:8000/api/autocomplete/${type}/${field}${ (value ? `/${value}` : '') }`)
            .then(response => {
              const { data } = response;

              // remove dups
              this.autocomplete = [ ...new Set(data.map(e => e[field])) ];
          });
        })();
      }, 1000);
    },

    onBlurEventHandler($event) {
      this.autocomplete = [];
    },

    setAutocompleteClick(type, field, value) {
      this.$emit('update:modelValue', value)
      this.autocomplete = [];
    },

    emitInterface() {
      this.$emit('interface', {
        clearAutoComplete: () => this.autocomplete = []
      });
    }
  }
}
</script>

<style scoped>

</style>
