<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, usePage } from "@inertiajs/vue3";
import { ref, onMounted, onUnmounted, computed, nextTick } from "vue";
import axios from "axios";
import BaseInput from "@/Components/BaseInput.vue";
const page = usePage();

// Utilisateur connecté
const user = ref(page.props.authUser || null); 
const users = ref(page.props.users || []);
const activeUserClass = (id) => receiverId.value === id ? 'bg-primary-menu font-bold text-white' : '';

// Messagerie
const receiverId = ref(null);
const receiverUser = ref(null);
const messages = ref([]);
const newMessage = ref('');  
const unreadCounts = ref({})  
const activeMenuId = ref(null); // ID du message dont le menu est ouvert
const editingMessageId = ref(null)
 
// Scroll automatique
const isUserScrolling = ref(false);

function toggleMenu(id) {
  activeMenuId.value = activeMenuId.value === id ? null : id;
}
 
// Supprimer un message
async function deleteMessage(id) { 

  try {
    await axios.delete(`/message/${id}`);
    messages.value = messages.value.filter(m => m.id !== id);
    activeMenuId.value = null; // fermer le menu
  } catch (err) {
    console.error("Erreur suppression message:", err);
  }
}

// Vérifie si on est tout en bas
function isAtBottom() {
  const container = document.querySelector(".messages-container");
  if (!container) return false;
  return (
    container.scrollTop + container.clientHeight >=
    container.scrollHeight - 5
  );
}

// Gérer le scroll manuel de l’utilisateur
function handleScroll() {
  const container = document.querySelector(".messages-container");
  if (!container) return;
  isUserScrolling.value = !isAtBottom();
}

// Scroll auto uniquement si on est déjà en bas
function scrollToBottom() {
  nextTick(() => {
    const container = document.querySelector(".messages-container");
    if (container && !isUserScrolling.value) {
      container.scrollTop = container.scrollHeight;
    }
  });
}
 
// Charger messages 
async function fetchMessages() {
  if (!receiverId.value) return;

  try {
    const res = await axios.get(`/api/messages/${receiverId.value}`);
    const fetched = Array.isArray(res.data) ? res.data : [];

    // Merge messages pour éviter doublons
    const map = new Map();
    messages.value.forEach(m => map.set(Number(m.id), { ...m }));
    fetched.forEach(f => {
      const id = Number(f.id);
      const existing = map.get(id) || {};
      map.set(id, {
        ...existing,
        ...f,
        id,
        sender_id: Number(f.sender_id),
        receiver_id: Number(f.receiver_id),
        is_received: f.is_received === true || f.is_received === 1 || f.is_received === "1",
        is_read: f.is_read === true || f.is_read === 1 || f.is_read === "1",
      });
    });

    // Convertir en tableau et trier par date
    messages.value = Array.from(map.values())
    .sort((a, b) => new Date(a.created_at) - new Date(b.created_at));

    scrollToBottom();
  } catch (err) {
    console.error("Erreur fetchMessages:", err);
  }
}
 
 
// Envoyer message
async function sendMessage() {
  if (!newMessage.value.trim() || !receiverUser.value) return;
  try {
    const res = await axios.post("/message", {
      receiver_id: receiverUser.value.id,
      content: newMessage.value
    });

    messages.value.push({
      ...res.data.message, 
      is_received: false,
      is_read: false
    });

    newMessage.value = "";
    scrollToBottom();
  } catch (e) {
    console.error("Erreur sendMessage:", e);
  }
}
 
const fetchUnread = async () => {
  try {
    const res = await axios.get('/messages/unread-by-user');
    const counts = {};
    res.data.forEach(item => {
      counts[item.sender_id] = item.unread_count;
    });
    unreadCounts.value = counts; // Important : réactif
  } catch (err) {
    console.error(err);
  }
};

// Polling toutes les 2s
let intervalUnread;
onMounted(() => {
  fetchUnread();
  intervalUnread = setInterval(fetchUnread, 2000);
});
onUnmounted(() => clearInterval(intervalUnread));
 
 
// Computed pour ajouter le nombre de non lus à chaque utilisateur
const otherUsersWithUnread = computed(() => {
  return users.value
    .filter(u => u.id !== user.value?.id)
    .map(u => ({
      ...u,
      unread: unreadCounts.value[u.id] || 0
    }));
});
 
// Lorsque l’utilisateur est sélectionné, réinitialiser son compteur
async function selectUser(userId) {
  receiverId.value = userId;
  receiverUser.value = users.value.find(u => u.id === userId) || null;

  await fetchMessages();
  
  try {

    await axios.post(`/message/markRead/${userId}`);

    // Mettre à jour localement les messages pour afficher ✅
    messages.value = messages.value.map(msg => {
      if(sameId(msg.sender_id, userId) && !msg.is_read) {
        return { ...msg, is_read: true };
      }
      return msg;
    });
 
    unreadCounts.value[userId] = 0;
  } catch (err) {
    console.error("Erreur markRead:", err);
  }
}
  
function sameId(a, b) {
  return Number(a) === Number(b);
}

function isMine(msg) {
  return sameId(msg.sender_id, user.value?.id);
}

function dayKey(value) {
  const date = new Date(value);
  if (Number.isNaN(date.getTime())) return "";
  return date.toDateString();
}
 
// Tous les messages précédents de la discussion courante
const filteredMessages = computed(() => {
  if (!receiverId.value || !user.value) return [];
  const me = Number(user.value.id);
  const other = Number(receiverId.value);
  return messages.value.filter((m) => {
    const sender = Number(m.sender_id);
    const receiver = Number(m.receiver_id);
    return (sender === other && receiver === me) || (sender === me && receiver === other);
  }).sort((a,b) => new Date(a.created_at) - new Date(b.created_at) || Number(a.id) - Number(b.id));
});
 
// Soumission du formulaire 
async function handleSubmit() {
  const content = newMessage.value.trim();
  if (!content) return;

  if (editingMessageId.value) {
    await updateMessage(editingMessageId.value, content);
  } else {
    await sendMessage();
  }
}
  
// Modifier un message existant
async function updateMessage(id, content) {
  try {
    const res = await axios.put(`/message/${id}`, { content });

    // Mettre à jour le message localement
    const msgIndex = messages.value.findIndex(m => m.id === id);
    if (msgIndex !== -1) {
      messages.value[msgIndex] = {
        ...messages.value[msgIndex],
        ...res.data.message  // contenu et autres infos mises à jour
      };
    }

    // Réinitialiser le formulaire
    editingMessageId.value = null;
    newMessage.value = "";

    scrollToBottom();
  } catch (err) {
    console.error("Erreur updateMessage:", err.response?.data || err.message);
  }
}

// Activer l’édition
function editMessage(msg) {
  editingMessageId.value = msg.id;
  newMessage.value = msg.content;
}
 
// Annuler l’édition
function cancelEdit() {
  editingMessageId.value = null
  newMessage.value = ""
}
 
</script>

<template>
  <Head title="Messagerie" />
  <AuthenticatedLayout>
    <template #header>
      <h2 class="text-xl font-semibold leading-tight text-primary-txt">
        Messagerie
      </h2>
    </template>

    <div class="py-12" v-if="user">
      <div class="flex justify-center">
        <div class="w-full max-w-8xl">
          <div class="bg-white shadow-md h-[600px] flex">
            
            <!-- Sidebar utilisateurs -->
            <!-- Sidebar utilisateurs -->
            <div class="w-1/3 border-r bg-white flex flex-col h-full">

              <!-- Header -->
              <div class="p-4 border-b bg-primary text-white font-semibold flex-shrink-0">
                Utilisateurs : {{ user.name }}
              </div>

              <!-- Liste scrollable -->
              <ul class="flex-1 overflow-y-auto">
                <li
                  v-for="u in otherUsersWithUnread"
                  :key="u.id"
                  @click="selectUser(u.id)"
                  class="hover:bg-primary-only hover:text-white hover:font-semibold
                        p-2 rounded flex items-center justify-between cursor-pointer"
                  :class="activeUserClass(u.id)"
                >
                  <div class="flex items-center gap-3 min-w-0">
                    <!-- Avatar -->
                    <div
                      class="w-8 h-8 flex-shrink-0 flex items-center justify-center
                            rounded-full bg-primary text-white font-bold"
                    >
                      {{ u.name.split(' ').map(n => n[0]).join('').toUpperCase() }}
                    </div>

                    <!-- Nom (ne déborde plus) -->
                    <span class="truncate">{{ u.name }}</span>

                    <!-- Badge non lus -->
                    <span
                      v-if="u.unread > 0"
                      class="bg-green-500 text-white text-xs font-bold
                            px-2 py-0.5 rounded-full ml-auto"
                    >
                      {{ u.unread }}
                    </span>
                  </div>
                </li>
              </ul>
            </div>

            <!-- Zone discussion -->
            <div class="w-2/3 flex flex-col">
              <!-- Header discussion -->
              <div class="p-4 border-b bg-primary text-white font-semibold">
                <span v-if="receiverUser">Discussion avec {{ receiverUser.name }}</span>
                <span v-else>Sélectionnez un utilisateur</span>
              </div> 
              <!-- Liste messages -->
              <div class="messages-container flex-1 p-4 overflow-y-auto flex flex-col space-y-2 bg-primary-layout"
                   style="background-image: url('/discut.jpg'); background-size: cover; background-position: center; padding-bottom: 1rem;"
                    @scroll="handleScroll">

                <div v-if="!receiverId" class="text-primary-txt text-center mt-10">
                  Choisissez un utilisateur pour commencer la discussion
                </div>
                <div v-else-if="filteredMessages.length === 0" class="text-primary-txt text-center mt-10">
                  Aucun message avec {{ receiverUser.name }}
                </div>

                <template v-for="(msg, index) in filteredMessages" :key="msg?.id">
                <p
                  v-if="index === 0 || dayKey(msg.created_at) !== dayKey(filteredMessages[index - 1].created_at)"
                  class="self-center rounded-full bg-white/80 px-3 py-1 text-xs text-primary-txt"
                >
                  {{ new Date(msg.created_at).toLocaleDateString() }}
                </p>
                <div class="flex w-full items-center relative"
                    :class="sameId(msg.sender_id, user.id) ? 'justify-end' : 'justify-start'">
                  <div 
                      :class="[
                        'px-3 py-2 max-w-xs rounded-xl relative', 
                        sameId(msg.sender_id, user.id) 
                        ? 'bg-[#d9f5e3] text-primary-txt' 
                        : 'bg-primary-form text-primary-txt'
                      ]" 
                    > 
                    <p class="text-sm">{{ msg.content }}</p> 


                    <!-- Indicateur lu pour les messages envoyés --> 
                     <div class="w-full flex justify-between items-end mt-1">
                      <span class="text-xs opacity-70  ">
                        {{ msg.created_at ? new Date(msg.created_at).toLocaleString([], { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' }) : '' }}
                      </span> 

                      <span v-if="sameId(msg.sender_id, user.id)" 
                            class="text-xs"
                            :class="msg.is_read ? 'text-blue-500' : 'text-blue-400'">
                        {{ msg.is_read ? '✓✓' : '✓' }}
                      </span> 
                     </div>
  
                    <!-- Trois points et menu uniquement pour tes messages -->
                    <template v-if="isMine(msg)">
                      <!-- Trois points -->
                      <span 
                        @click="toggleMenu(msg.id)" 
                        class="absolute top-0 right-0 cursor-pointer text-black font-bold hover:text-primary-txt
                        w-6 h-6 flex items-center justify-center rounded-full hover:bg-primary-only transition-colors duration-200"
                        
                        title="Options"
                      >
                        ⋮
                      </span> 
                      <!-- Menu contextuel -->
                        <div 
                          v-if="activeMenuId === msg.id" 
                          class="absolute top-1 right-10 bg-white p-2 shadow-lg border rounded-lg z-20 w-32 
                                animate-fade-in"
                        >
                          <ul class="text-sm">
                            <li 
                              @click="deleteMessage(msg.id)" 
                              class="px-3 py-2 hover:bg-red-100 cursor-pointer border-b border-primary-only text-red-500 flex items-center gap-2 rounded-md transition"
                            >
                              🗑Supprimer
                            </li>
                            <li 
                              @click="editMessage(msg)" 
                              class="px-3 py-2 hover:bg-green-100 cursor-pointer text-green-600 flex items-center gap-2 rounded-md transition"
                            >
                              ✏️Modifier
                            </li>
                          </ul>
                        </div> 
                    </template> 
                  </div>
                </div>
                </template>
              </div> 
              <!-- Formulaire -->
              <form v-if="receiverId" @submit.prevent="handleSubmit" class="flex items-end space-x-2 border-primary-only p-4">
                <BaseInput
                  class="min-w-0 flex-1"
                  name="message"
                  v-model="newMessage"
                  :placeholder="editingMessageId ? 'Modifier le message...' : 'Votre message...'"
                />
                <button 
                  type="submit" 
                  class="bg-primary text-white px-3 py-2 rounded"
                >
                   {{ editingMessageId ? 'Mettre à jour' : 'Envoyer' }}
                </button>

                <button 
                  v-if="editingMessageId" 
                  type="button" 
                  @click="cancelEdit" 
                  class="bg-primary-menu text-white px-3 py-2 rounded"
                >
                  Retour
                </button>
              </form>
            </div>

          </div>
        </div>
      </div>
    </div>

    <div v-else class="text-center py-20 text-primary-txt">
      Chargement de l'utilisateur...
    </div>
  </AuthenticatedLayout>
</template>

<style scoped>
@keyframes fadeIn {
  from { opacity: 0; transform: scale(0.95); }
  to { opacity: 1; transform: scale(1); }
}
.animate-fade-in {
  animation: fadeIn 0.15s ease-out;
}
</style>

