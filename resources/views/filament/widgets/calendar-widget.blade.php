<x-filament-widgets::widget class="h-full">
    <div class="fi-section rounded-xl bg-white shadow-sm ring-1 ring-gray-950/5 dark:bg-gray-900 dark:ring-white/10 h-full flex flex-col">
        <div class="p-4 flex-1 flex flex-col">
            <h2 class="text-xl font-bold text-center mb-4">Tanggal & Waktu</h2>

            <div class="flex-1 flex flex-col justify-center" x-data="{
                currentDate: new Date(),

                formatDate(date) {
                    const options = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
                    return date.toLocaleDateString('id-ID', options);
                },

                formatTime(date) {
                    const options = { hour: '2-digit', minute: '2-digit', second: '2-digit' };
                    return date.toLocaleTimeString('id-ID', options);
                },

                updateTime() {
                    this.currentDate = new Date();
                    setTimeout(() => this.updateTime(), 1000);
                }
            }" x-init="updateTime()">
                <div class="text-center mb-6">
                    <div class="text-2xl font-bold text-primary-600" x-text="formatDate(currentDate)"></div>
                    <div class="text-4xl font-mono font-bold mt-3 text-gray-800 dark:text-gray-200" x-text="formatTime(currentDate)"></div>
                </div>

                <div class="grid grid-cols-7 gap-1 mt-4">
                    <template x-for="day in ['Min', 'Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab']">
                        <div class="text-center font-medium py-1 text-sm text-gray-500 dark:text-gray-400" x-text="day"></div>
                    </template>

                    <template x-for="i in new Date(currentDate.getFullYear(), currentDate.getMonth(), 1).getDay()">
                        <div class="h-8 py-1"></div>
                    </template>

                    <template x-for="day in new Date(currentDate.getFullYear(), currentDate.getMonth() + 1, 0).getDate()">
                        <div class="text-center py-1 h-8 text-sm" :class="{ 'bg-primary-500 text-white rounded-full font-bold': day === currentDate.getDate() }" x-text="day"></div>
                    </template>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
