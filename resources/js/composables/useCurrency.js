import { computed, unref } from 'vue'
import { usePage } from '@inertiajs/vue3'

export function useCurrency(overrideCode = null) {
  const page = usePage()
  const workspace = computed(() => page.props.currency || { code: 'USD', symbol: '$' })
  const currencies = computed(() => page.props.currencies || [])

  const resolved = computed(() => {
    const code = unref(overrideCode) || workspace.value.code
    const match = currencies.value.find((option) => option.code === code)

    if (match) {
      return { code: match.code, symbol: match.symbol }
    }

    return code === workspace.value.code
      ? workspace.value
      : { code, symbol: workspace.value.symbol }
  })

  function formatCurrency(amount, options = {}) {
    const value = Number(amount || 0)
    const currencyCode = options.currency || resolved.value.code
    const match = currencies.value.find((option) => option.code === currencyCode)
    const symbol = match?.symbol || resolved.value.symbol
    const maximumFractionDigits = options.maximumFractionDigits ?? 0
    const minimumFractionDigits = options.minimumFractionDigits ?? maximumFractionDigits

    try {
      return new Intl.NumberFormat('en-US', {
        style: 'currency',
        currency: currencyCode,
        maximumFractionDigits,
        minimumFractionDigits,
      }).format(value)
    } catch {
      return `${symbol}${value.toLocaleString('en-US', { maximumFractionDigits, minimumFractionDigits })}`
    }
  }

  return {
    code: computed(() => resolved.value.code),
    symbol: computed(() => resolved.value.symbol),
    currencies,
    formatCurrency,
  }
}
