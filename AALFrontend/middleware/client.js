export default function ({ store, redirect }) {
  // If the user is not authenticated
  if (store.$auth.$state.user.scope !== 'Client') {
    return redirect('/')
  }
}
