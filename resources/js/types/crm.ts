export type OwnerRef = {
    id: number;
    name: string;
};

export type SelectOption = {
    value: number | string;
    label: string;
};

export type PaginationLink = {
    url: string | null;
    label: string;
    active: boolean;
};

export type Paginated<T> = {
    data: T[];
    links: PaginationLink[];
    current_page: number;
    last_page: number;
    from: number | null;
    to: number | null;
    total: number;
};

export type CompanyListItem = {
    id: number;
    name: string;
    domain: string | null;
    industry: string | null;
    city: string | null;
    phone: string | null;
    owner: OwnerRef | null;
};

export type CompanyDetail = {
    id: number;
    name: string;
    domain: string | null;
    industry: string | null;
    phone: string | null;
    website: string | null;
    address: string | null;
    city: string | null;
    postal_code: string | null;
    country: string | null;
    owner_id: number | null;
    owner: OwnerRef | null;
    custom_fields: CustomFieldValues;
    created_at: string | null;
};

export type CompanyRef = {
    id: number;
    name: string;
};

export type ContactListItem = {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string | null;
    phone: string | null;
    job_title: string | null;
    company: CompanyRef | null;
    owner: OwnerRef | null;
};

export type ContactDetail = {
    id: number;
    first_name: string;
    last_name: string;
    full_name: string;
    email: string | null;
    phone: string | null;
    job_title: string | null;
    company_id: number | null;
    company: CompanyRef | null;
    owner_id: number | null;
    owner: OwnerRef | null;
    custom_fields: CustomFieldValues;
    created_at: string | null;
};

export type MorphTarget = {
    type: 'company' | 'contact' | 'deal';
    id: number;
};

export type ActivityItem = {
    id: number;
    type: string;
    type_label: string;
    subject: string | null;
    body: string | null;
    occurred_at: string;
    user: OwnerRef | null;
};

export type DealCard = {
    id: number;
    name: string;
    amount: number | null;
    currency: string;
    position: number;
    company: CompanyRef | null;
    contact: OwnerRef | null;
    owner: OwnerRef | null;
};

export type BoardStage = {
    id: number;
    name: string;
    key: string;
    color: string | null;
    is_won: boolean;
    is_lost: boolean;
    total_amount: number;
    deals: DealCard[];
};

export type PipelineRef = {
    id: number;
    name: string;
};

export type PipelineWithStages = {
    id: number;
    name: string;
    stages: SelectOption[];
};

export type DealDetail = {
    id: number;
    name: string;
    amount: number | null;
    currency: string;
    status: string;
    status_label: string;
    pipeline_id: number;
    pipeline_stage_id: number;
    stage: { id: number; name: string; color: string | null } | null;
    company_id: number | null;
    company: CompanyRef | null;
    contact_id: number | null;
    contact: OwnerRef | null;
    owner_id: number | null;
    owner: OwnerRef | null;
    expected_close_date: string | null;
    custom_fields: CustomFieldValues;
    created_at: string | null;
};

export type CustomFieldType =
    | 'text'
    | 'textarea'
    | 'number'
    | 'date'
    | 'select'
    | 'multiselect'
    | 'checkbox'
    | 'email'
    | 'url'
    | 'phone'
    | 'relation';

export type CustomFieldChoice = {
    value: string;
    label: string;
};

export type CustomFieldDefinition = {
    id: number;
    key: string;
    label: string;
    type: CustomFieldType;
    options: {
        choices?: CustomFieldChoice[] | { value: number; label: string }[];
        related_module?: string;
    };
    is_required: boolean;
    is_filterable: boolean;
    position: number;
    help_text: string | null;
};

export type CustomFieldValue = string | number | boolean | string[] | null;

export type CustomFieldValues = Record<string, CustomFieldValue>;

export type TaskItem = {
    id: number;
    title: string;
    description: string | null;
    due_at: string | null;
    priority: string;
    priority_label: string;
    is_completed: boolean;
    completed_at: string | null;
    assignee: OwnerRef | null;
    related?: { type: string; id: number; label: string } | null;
};
