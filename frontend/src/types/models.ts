export interface Admin {
  id: string;
  name: string;
  email: string;
  role: 'super_admin' | 'admin_sppg' | 'admin_school';
  entity_id: string | null;
  entity_type: string | null;
  is_active: boolean;
  created_at: string;
  updated_at: string;
}

export interface SppgProvider {
  id: string;
  name: string;
  license_number: string;
  address: string;
  city: string;
  province: string;
  phone: string;
  email: string;
  is_active: boolean;
  capacity_per_day: number;
  schools_count?: number;
  created_at: string;
  updated_at: string;
}

export interface School {
  id: string;
  name: string;
  npsn: string;
  address: string;
  city: string;
  province: string;
  phone: string;
  email: string;
  level: 'sd' | 'smp' | 'sma' | 'smk';
  student_count: number;
  geofence_radius_meters: number;
  is_active: boolean;
  sppg_provider_id: string;
  sppg_provider?: SppgProvider;
  created_at: string;
  updated_at: string;
}

export interface MbgMenu {
  id: string;
  sppg_provider_id: string;
  sppg_provider?: SppgProvider;
  menu_name: string;
  description: string;
  serve_date: string;
  meal_type: 'breakfast' | 'lunch';
  nutrition_data: Record<string, unknown>;
  photo_path: string | null;
  calories: number;
  protein_g: number;
  carbs_g: number;
  fat_g: number;
  created_at: string;
  updated_at: string;
}

export interface DailyDispatch {
  id: string;
  sppg_provider_id: string;
  school_id: string;
  mbg_menu_id: string;
  dispatch_date: string;
  quantity_sent: number;
  vehicle_plate: string;
  driver_name: string;
  dispatched_at: string;
  photo_proof_path: string | null;
  sppg_provider?: SppgProvider;
  school?: School;
  menu?: MbgMenu;
  created_at: string;
}

export interface SchoolReceipt {
  id: string;
  daily_dispatch_id: string;
  school_id: string;
  receipt_date: string;
  quantity_received: number;
  quantity_distributed: number;
  quantity_damaged: number;
  condition: string;
  notes: string | null;
  school?: School;
  created_at: string;
}

export interface UserFeedback {
  id: string;
  school_id: string;
  mbg_menu_id: string;
  feedback_date: string;
  zkp_identity_hash: string;
  rating: number;
  taste_rating: number;
  portion_rating: number;
  comment: string | null;
  school?: School;
  menu?: MbgMenu;
  created_at: string;
}

export interface PaginatedResponse<T> {
  data: T[];
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };
  meta: {
    current_page: number;
    from: number;
    last_page: number;
    per_page: number;
    to: number;
    total: number;
  };
}

export interface LoginRequest {
  email: string;
  password: string;
}

export interface LoginResponse {
  token: string;
  admin: Admin;
}
