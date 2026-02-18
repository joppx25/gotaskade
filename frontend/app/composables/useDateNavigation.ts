import { computed, ref } from 'vue'

export interface DateEntry {
  date: string
  label: string
  isToday: boolean
}

export interface DateGroup {
  label: string
  dates: DateEntry[]
}

function formatDateLabel(dateStr: string, today: Date): string {
  const date = new Date(dateStr + 'T00:00:00')
  const todayStr = today.toISOString().split('T')[0]
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)
  const yesterdayStr = yesterday.toISOString().split('T')[0]

  if (dateStr === todayStr) return 'Today'
  if (dateStr === yesterdayStr) return 'Yesterday'

  const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday']
  const months = ['January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December']

  const dayName = days[date.getDay()]
  const monthName = months[date.getMonth()]
  const dayNum = date.getDate()

  return `${dayName}, ${monthName} ${dayNum}`
}

function getWeekLabel(dateStr: string, today: Date): string {
  const date = new Date(dateStr + 'T00:00:00')
  const todayStr = today.toISOString().split('T')[0]
  const yesterday = new Date(today)
  yesterday.setDate(yesterday.getDate() - 1)
  const yesterdayStr = yesterday.toISOString().split('T')[0]

  if (dateStr === todayStr || dateStr === yesterdayStr) return 'current'

  const diffTime = today.getTime() - date.getTime()
  const diffDays = Math.floor(diffTime / (1000 * 60 * 60 * 24))

  if (diffDays < 7) return 'Last week'

  const weekOfMonth = Math.ceil(date.getDate() / 7)
  const months = ['January', 'February', 'March', 'April', 'May', 'June',
    'July', 'August', 'September', 'October', 'November', 'December']

  const ordinals = ['1st', '2nd', '3rd', '4th', '5th']
  return `${ordinals[weekOfMonth - 1] || weekOfMonth + 'th'} Week of ${months[date.getMonth()]}`
}

export function useDateNavigation() {
  const selectedDate = ref<string>(new Date().toISOString().split('T')[0])
  const today = new Date()

  const dateRange = computed((): DateEntry[] => {
    const dates: DateEntry[] = []
    const todayStr = today.toISOString().split('T')[0]

    for (let i = 0; i < 30; i++) {
      const d = new Date(today)
      d.setDate(d.getDate() - i)
      const dateStr = d.toISOString().split('T')[0]
      dates.push({
        date: dateStr,
        label: formatDateLabel(dateStr, today),
        isToday: dateStr === todayStr,
      })
    }

    return dates
  })

  const groupedDates = computed((): DateGroup[] => {
    const groups: DateGroup[] = []
    let currentGroupLabel = ''

    for (const entry of dateRange.value) {
      const groupLabel = getWeekLabel(entry.date, today)

      if (groupLabel !== currentGroupLabel) {
        currentGroupLabel = groupLabel
        if (groupLabel !== 'current') {
          groups.push({ label: groupLabel, dates: [entry] })
        }
        else {
          const existingCurrent = groups.find(g => g.label === 'current')
          if (existingCurrent) {
            existingCurrent.dates.push(entry)
          }
          else {
            groups.push({ label: 'current', dates: [entry] })
          }
        }
      }
      else {
        const lastGroup = groups[groups.length - 1]
        if (lastGroup) {
          lastGroup.dates.push(entry)
        }
      }
    }

    return groups
  })

  function selectDate(date: string) {
    selectedDate.value = date
  }

  return {
    selectedDate,
    dateRange,
    groupedDates,
    selectDate,
  }
}
