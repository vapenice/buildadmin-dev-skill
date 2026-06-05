import { baTableApi } from '/@/api/common'
import type { ApiReturn } from '/@/api/common'

// ============================================================
// 接口类型定义（禁止使用 any）
// ============================================================

/** 列表查询参数 */
export interface [ModelName]QueryParams {
    page?: number
    limit?: number
    keyword?: string
    status?: 0 | 1 | ''
    create_time?: string[]
    [key: string]: unknown
}

/** 单条记录结构 */
export interface [ModelName]Item {
    id: number
    title: string
    status: 0 | 1
    cover_image: string
    create_time: number
    update_time: number
    // 在此处补充其他业务字段
}

/** 列表响应 */
export interface [ModelName]ListResponse {
    list: [ModelName]Item[]
    total: number
    remark: string
}

/** 新增/编辑表单 */
export interface [ModelName]Form {
    title: string
    status: 0 | 1
    cover_image?: string
    // 在此处补充其他表单字段
}

// ============================================================
// 接口方法封装
// 使用项目封装的 baTableApi，禁止直接调用原生 axios
// ============================================================

const api = new baTableApi('/admin/[module]/[controller]/')

/** 获取列表 */
export const get[ModelName]List = (params: [ModelName]QueryParams): Promise<ApiReturn<[ModelName]ListResponse>> => {
    return api.index(params)
}

/** 新增记录 */
export const create[ModelName] = (data: [ModelName]Form): Promise<ApiReturn> => {
    return api.add(data)
}

/** 编辑记录 */
export const update[ModelName] = (id: number, data: Partial<[ModelName]Form>): Promise<ApiReturn> => {
    return api.edit({ id, ...data })
}

/** 删除记录 */
export const delete[ModelName] = (ids: number[]): Promise<ApiReturn> => {
    return api.del({ ids })
}
