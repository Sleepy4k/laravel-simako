export interface Role {
    id: number;
    name: 'admin' | 'tenant' | 'pengguna';
}

export interface UserProfile {
    user_id: number;
    name: string;
    avatar: string | null;
    id_card_number: string | null;
    id_card_image: string | null;
    gender: 'male' | 'female' | null;
    birth_date: string | null;
}

export interface TenantProfile {
    user_id: number;
    business_name: string;
    identity_image: string | null;
    is_verified: boolean;
    is_suspended: boolean;
    verified_at: string | null;
    suspended_at: string | null;
    suspension_reason: string | null;
}

export interface BankAccount {
    id: number;
    user_id: number;
    bank_name: string;
    account_number: string;
    account_holder: string;
    is_primary: boolean;
}

export interface User {
    id: number;
    role_id: number;
    email: string;
    phone: string | null;
    is_active: boolean;
    email_verified_at: string | null;
    role?: Role;
    userProfile?: UserProfile;
    tenantProfile?: TenantProfile;
    bankAccounts?: BankAccount[];
}

export interface Address {
    id: number;
    addressable_type: string;
    addressable_id: number;
    street: string | null;
    district: string | null;
    city: string;
    province: string;
    postal_code: string | null;
    latitude: number | null;
    longitude: number | null;
}

export interface FacilityCategory {
    id: number;
    name: string;
    icon: string;
}

export interface Facility {
    id: number;
    facility_category_id: number;
    name: string;
    icon: string;
    category?: FacilityCategory;
}

export interface KostImage {
    id: number;
    kost_id: number;
    path: string;
    caption: string | null;
    sort_order: number;
}

export interface RoomImage {
    id: number;
    room_id: number;
    path: string;
    caption: string | null;
    sort_order: number;
}

export interface RoomPrice {
    id: number;
    room_id: number;
    period: 'monthly' | 'quarterly' | 'semi_annual' | 'annual';
    price: number;
    deposit: number;
}

export interface Room {
    id: number;
    kost_id: number;
    name: string;
    floor: number | null;
    size_sqm: number | null;
    is_available: boolean;
    prices?: RoomPrice[];
    images?: RoomImage[];
    facilities?: Facility[];
    kost?: Kost;
}

export interface Kost {
    id: number;
    user_id: number;
    name: string;
    slug: string;
    description: string | null;
    type: 'putra' | 'putri' | 'campur';
    status: 'draft' | 'active' | 'inactive';
    thumbnail: string | null;
    total_rooms: number;
    available_rooms: number;
    address?: Address;
    images?: KostImage[];
    rooms?: Room[];
    facilities?: Facility[];
    tenant?: User;
    reviews?: Review[];
}

export interface PaymentProof {
    id: number;
    payment_id: number;
    path: string;
    uploaded_at: string;
}

export interface Payment {
    id: number;
    booking_id: number;
    period_start: string;
    period_end: string;
    amount: number;
    status: 'unpaid' | 'pending_verification' | 'paid' | 'declined';
    due_date: string;
    paid_at: string | null;
    declined_at: string | null;
    decline_notes: string | null;
    proofs?: PaymentProof[];
    booking?: Booking;
}

export interface Message {
    id: number;
    message_thread_id: number;
    user_id: number;
    body: string;
    read_at: string | null;
    created_at: string;
    sender?: User;
}

export interface MessageThread {
    id: number;
    booking_id: number;
    created_at: string;
    booking?: Booking;
    messages?: Message[];
}

export interface Review {
    id: number;
    booking_id: number;
    user_id: number;
    rating: 1 | 2 | 3 | 4 | 5;
    comment: string | null;
    user?: User;
}

export interface Booking {
    id: number;
    user_id: number;
    room_id: number;
    room_price_id: number;
    status: 'pending' | 'approved' | 'active' | 'ended' | 'cancelled';
    start_date: string;
    end_date: string | null;
    notes: string | null;
    approved_at: string | null;
    cancelled_at: string | null;
    cancellation_reason: string | null;
    user?: User;
    room?: Room;
    roomPrice?: RoomPrice;
    payments?: Payment[];
    messageThread?: MessageThread;
    review?: Review;
}

export interface Paginated<T> {
    data: T[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    from: number | null;
    to: number | null;
    links: { url: string | null; label: string; active: boolean }[];
}
