import { usePage } from "@inertiajs/vue3";
import { watch } from "vue";
import { toast } from 'vue3-toastify';

let seen = new Set(); // tracks shown flash IDs
export function useFlash() {
    const page = usePage();
    watch(
        () => page.props.flash,
        (flash) => {
            if (!flash) return;
            // success

            if(flash.success && !seen.has(flash.success.id)) {
                seen.add(flash.success.id);
                toast.success(flash.success.message);
            }

            // error
            if(flash.error && !seen.has(flash.error.id)) {
                seen.add(flash.error.id);
                toast.error(flash.error.message);
            }
        },
        { immediate:true }
    )

}